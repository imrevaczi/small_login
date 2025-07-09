<?php

use PHPUnit\Framework\TestCase;

/**
 * Integration tests for the Small Login System
 * These tests require a working database connection
 */
class IntegrationTest extends TestCase
{
    private $db;
    private $testUsername = 'integration_test_user';
    private $testEmail = 'integration@test.com';
    private $testPassword = 'TestPass123';

    protected function setUp(): void
    {
        resetGlobalState();
        
        if (!$this->isDatabaseAvailable()) {
            $this->markTestSkipped('Database not available for integration tests');
        }
        
        $this->db = new Db();
        $GLOBALS['Db'] = $this->db;
        
        // Set up clean test database
        $this->setupCleanDatabase();
    }

    public function testFullUserRegistrationAndLoginFlow()
    {
        // Test 1: Register a new user
        $_POST = [
            'register' => '1',
            'username' => $this->testUsername,
            'email' => $this->testEmail,
            'firstname' => 'Integration',
            'lastname' => 'Test',
            'mobile' => '1234567890',
            'password' => $this->testPassword,
            'confirm' => $this->testPassword
        ];
        
        // Mock empty database for first user registration
        $user = $this->createUserForTesting();
        
        // Simulate registration (without constructor side effects)
        $user->register();
        
        // Check that no errors occurred during registration
        $this->assertEquals(0, $user->count_errors(), 
            'Registration should succeed: ' . $user->display_errors());
        
        // Test 2: Verify user was created in database
        $userInfo = $user->get_user_info($this->testUsername);
        $this->assertNotEmpty($userInfo);
        $this->assertEquals($this->testUsername, $userInfo['username']);
        $this->assertEquals($this->testEmail, $userInfo['email']);
        $this->assertEquals('Integration', $userInfo['firstname']);
        $this->assertEquals('Test', $userInfo['lastname']);
        
        // Test 3: Verify password was hashed
        $this->assertNotEquals($this->testPassword, $userInfo['password']);
        $this->assertTrue(password_verify($this->testPassword, $userInfo['password']));
        
        // Test 4: Test login with correct credentials
        resetGlobalState();
        $_POST = [
            'login' => '1',
            'username' => $this->testUsername,
            'password' => $this->testPassword
        ];
        
        $loginUser = $this->createUserForTesting();
        $loginUser->login();
        
        $this->assertEquals(0, $loginUser->count_errors(), 
            'Login should succeed: ' . $loginUser->display_errors());
        
        // Test 5: Test login with wrong password
        resetGlobalState();
        $_POST = [
            'login' => '1',
            'username' => $this->testUsername,
            'password' => 'wrongpassword'
        ];
        
        $failUser = $this->createUserForTesting();
        $failUser->login();
        
        $this->assertGreaterThan(0, $failUser->count_errors());
        $this->assertStringContains('Wrong user or password', $failUser->display_errors());
    }

    public function testDatabaseSetupAndTableCreation()
    {
        // Clean up any existing table
        cleanupTestDatabase();
        
        // Test database setup
        $result = Db::checkSetup();
        $this->assertTrue($result, 'Database setup should succeed');
        
        // Verify table structure
        $fields = Db::getUserFields('user');
        $this->assertIsArray($fields);
        
        $fieldNames = array_column($fields, 'Field');
        $expectedFields = ['username', 'firstname', 'lastname', 'email', 'mobile', 'password'];
        
        foreach ($expectedFields as $expectedField) {
            $this->assertContains($expectedField, $fieldNames, 
                "Field '$expectedField' should exist in user table");
        }
        
        // Verify ID field is excluded from getUserFields
        $this->assertNotContains('ID', $fieldNames, 'ID field should be excluded');
    }

    public function testUserValidationWithRealData()
    {
        // Test email validation
        $validEmails = ['test@example.com', 'user.name@domain.co.uk'];
        $invalidEmails = ['invalid-email', '@domain.com', 'user@'];
        
        foreach ($validEmails as $email) {
            $this->assertTrue(Validate::valid_email($email), 
                "Email '$email' should be valid");
        }
        
        foreach ($invalidEmails as $email) {
            $this->assertFalse(Validate::valid_email($email), 
                "Email '$email' should be invalid");
        }
        
        // Test password validation
        $errors = [];
        $this->assertTrue(Validate::checkPassword('ValidPass123', $errors));
        $this->assertEmpty($errors);
        
        $errors = [];
        $this->assertFalse(Validate::checkPassword('short', $errors));
        $this->assertNotEmpty($errors);
    }

    public function testDuplicateUserRegistration()
    {
        // First registration
        $_POST = [
            'register' => '1',
            'username' => $this->testUsername,
            'email' => $this->testEmail,
            'firstname' => 'First',
            'lastname' => 'User',
            'mobile' => '1111111111',
            'password' => $this->testPassword,
            'confirm' => $this->testPassword
        ];
        
        $user1 = $this->createUserForTesting();
        $user1->register();
        $this->assertEquals(0, $user1->count_errors());
        
        // Second registration with same username
        resetGlobalState();
        $_POST = [
            'register' => '1',
            'username' => $this->testUsername,
            'email' => 'different@email.com',
            'firstname' => 'Second',
            'lastname' => 'User',
            'mobile' => '2222222222',
            'password' => 'DifferentPass123',
            'confirm' => 'DifferentPass123'
        ];
        
        $user2 = $this->createUserForTesting();
        $user2->register();
        
        $this->assertGreaterThan(0, $user2->count_errors());
        $this->assertStringContains('Username already exists', $user2->display_errors());
    }

    public function testEmptyDatabaseDetection()
    {
        // Clean database
        cleanupTestDatabase();
        Db::checkSetup(); // Create table but no users
        
        $user = $this->createUserForTesting();
        $this->assertTrue($user->empty_db(), 'Database should be empty');
        
        // Add a user
        $_POST = [
            'register' => '1',
            'username' => $this->testUsername,
            'email' => $this->testEmail,
            'firstname' => 'Test',
            'lastname' => 'User',
            'mobile' => '1234567890',
            'password' => $this->testPassword,
            'confirm' => $this->testPassword
        ];
        
        $user->register();
        
        // Check database is no longer empty
        $user2 = $this->createUserForTesting();
        $this->assertFalse($user2->empty_db(), 'Database should not be empty after user registration');
    }

    private function isDatabaseAvailable()
    {
        try {
            $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
            if ($connection) {
                mysqli_close($connection);
                return true;
            }
        } catch (Exception $e) {
            // Database not available
        }
        return false;
    }

    private function setupCleanDatabase()
    {
        cleanupTestDatabase();
        Db::checkSetup();
    }

    private function createUserForTesting()
    {
        // Create User object without triggering constructor logic
        $user = $this->getMockBuilder(User::class)
                     ->disableOriginalConstructor()
                     ->onlyMethods([])
                     ->getMock();
        
        // Set required properties
        $reflection = new ReflectionClass($user);
        
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($user, $this->db);
        
        $errorProperty = $reflection->getProperty('error');
        $errorProperty->setAccessible(true);
        $errorProperty->setValue($user, []);
        
        $isLoggedInProperty = $reflection->getProperty('is_logged_in');
        $isLoggedInProperty->setAccessible(true);
        $isLoggedInProperty->setValue($user, false);
        
        return $user;
    }

    protected function tearDown(): void
    {
        cleanupTestDatabase();
        resetGlobalState();
        unset($GLOBALS['Db']);
    }
}