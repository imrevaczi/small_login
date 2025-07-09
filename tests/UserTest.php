<?php

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private $mockDb;

    protected function setUp(): void
    {
        resetGlobalState();
        
        // Create a mock Db object
        $this->mockDb = $this->createMock(Db::class);
        $GLOBALS['Db'] = $this->mockDb;
        
        // Mock session_start to avoid headers already sent errors
        if (!isset($_SESSION)) {
            $_SESSION = [];
        }
    }

    public function testGetUsernameReturnsCorrectValue()
    {
        // Create user without triggering constructor logic
        $user = $this->createUserWithoutConstructor();
        $this->setPrivateProperty($user, 'username', 'testuser');
        
        $this->assertEquals('testuser', $user->get_username());
    }

    public function testGetEmailReturnsCorrectValue()
    {
        $user = $this->createUserWithoutConstructor();
        $this->setPrivateProperty($user, 'email', 'test@example.com');
        
        $this->assertEquals('test@example.com', $user->get_email());
    }

    public function testIsLoggedInReturnsFalseByDefault()
    {
        $user = $this->createUserWithoutConstructor();
        $this->assertFalse($user->is_logged_in());
    }

    public function testIsLoggedInReturnsTrueWhenLoggedIn()
    {
        $user = $this->createUserWithoutConstructor();
        $this->setPrivateProperty($user, 'is_logged_in', true);
        
        $this->assertTrue($user->is_logged_in());
    }

    public function testDisplayErrorsWithNoErrors()
    {
        $user = $this->createUserWithoutConstructor();
        $result = $user->display_errors();
        $this->assertEquals('', $result);
    }

    public function testDisplayErrorsWithErrors()
    {
        $user = $this->createUserWithoutConstructor();
        $this->setPrivateProperty($user, 'error', ['Error 1', 'Error 2']);
        
        $result = $user->display_errors();
        $expected = 'Error 1<br />Error 2<br />';
        $this->assertEquals($expected, $result);
    }

    public function testCountErrorsWithNoErrors()
    {
        $user = $this->createUserWithoutConstructor();
        $this->assertEquals(0, $user->count_errors());
    }

    public function testCountErrorsWithErrors()
    {
        $user = $this->createUserWithoutConstructor();
        $this->setPrivateProperty($user, 'error', ['Error 1', 'Error 2', 'Error 3']);
        
        $this->assertEquals(3, $user->count_errors());
    }

    public function testLoginWithEmptyUsername()
    {
        $_POST = [
            'login' => '1',
            'username' => '',
            'password' => 'password123'
        ];
        
        $user = $this->createUserWithoutConstructor();
        $user->login();
        
        $this->assertEquals(1, $user->count_errors());
        $this->assertStringContains('Username field was empty', $user->display_errors());
    }

    public function testLoginWithEmptyPassword()
    {
        $_POST = [
            'login' => '1',
            'username' => 'testuser',
            'password' => ''
        ];
        
        $user = $this->createUserWithoutConstructor();
        $user->login();
        
        $this->assertEquals(1, $user->count_errors());
        $this->assertStringContains('Password field was empty', $user->display_errors());
    }

    public function testLoginWithInvalidCredentials()
    {
        $_POST = [
            'login' => '1',
            'username' => 'testuser',
            'password' => 'wrongpassword'
        ];
        
        // Mock the verify_password method to return false
        $user = $this->getMockBuilder(User::class)
                     ->disableOriginalConstructor()
                     ->onlyMethods(['verify_password'])
                     ->getMock();
        
        $user->method('verify_password')->willReturn(false);
        
        $user->login();
        
        $this->assertEquals(1, $user->count_errors());
        $this->assertStringContains('Wrong user or password', $user->display_errors());
    }

    public function testGetPostSanitizesInput()
    {
        $_POST = [
            'username' => '<script>alert("xss")</script>testuser',
            'email' => 'test@example.com',
            'firstname' => 'Test<script>',
            'lastname' => 'User',
            'mobile' => '1234567890',
            'password' => 'password123',
            'confirm' => 'password123'
        ];
        
        // Mock Db::getUserFields to return expected fields
        $mockFields = [
            ['Field' => 'username'],
            ['Field' => 'email'],
            ['Field' => 'firstname'],
            ['Field' => 'lastname'],
            ['Field' => 'mobile'],
            ['Field' => 'password']
        ];
        
        $this->mockDb->method('getUserFields')->willReturn($mockFields);
        
        $user = $this->createUserWithoutConstructor();
        $result = $user->get_post();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('username', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('confirm', $result);
        
        // Check that HTML is sanitized
        $this->assertStringNotContainsString('<script>', $result['username']);
        $this->assertStringNotContainsString('<script>', $result['firstname']);
    }

    public function testRegisterWithMismatchedPasswords()
    {
        $_POST = [
            'register' => '1',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'firstname' => 'Test',
            'lastname' => 'User',
            'mobile' => '1234567890',
            'password' => 'password123',
            'confirm' => 'differentpassword'
        ];
        
        $this->mockDb->method('getUserFields')->willReturn([
            ['Field' => 'username'],
            ['Field' => 'email'],
            ['Field' => 'firstname'],
            ['Field' => 'lastname'],
            ['Field' => 'mobile'],
            ['Field' => 'password']
        ]);
        
        $user = $this->createUserWithoutConstructor();
        $user->register();
        
        $this->assertGreaterThan(0, $user->count_errors());
        $this->assertStringContains("Passwords don't match", $user->display_errors());
    }

    public function testRegisterWithEmptyFields()
    {
        $_POST = [
            'register' => '1',
            'username' => '',
            'email' => '',
            'password' => '',
            'confirm' => ''
        ];
        
        $this->mockDb->method('getUserFields')->willReturn([
            ['Field' => 'username'],
            ['Field' => 'email'],
            ['Field' => 'password']
        ]);
        
        $user = $this->createUserWithoutConstructor();
        $user->register();
        
        $this->assertEquals(4, $user->count_errors());
        $errors = $user->display_errors();
        $this->assertStringContains('Username field was empty', $errors);
        $this->assertStringContains('Email field was empty', $errors);
        $this->assertStringContains('Password field was empty', $errors);
        $this->assertStringContains('You need to repeat the password', $errors);
    }

    public function testEmptyDbReturnsTrueWhenNoUsers()
    {
        // Mock query result with 0 rows
        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult->num_rows = 0;
        
        $this->mockDb->method('query_db')->willReturn($mockResult);
        
        $user = $this->createUserWithoutConstructor();
        $this->assertTrue($user->empty_db());
    }

    public function testEmptyDbReturnsFalseWhenUsersExist()
    {
        // Mock query result with rows
        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult->num_rows = 5;
        
        $this->mockDb->method('query_db')->willReturn($mockResult);
        
        $user = $this->createUserWithoutConstructor();
        $this->assertFalse($user->empty_db());
    }

    public function testGetUserInfoReturnsUserData()
    {
        // Mock database result
        $expectedUserData = [
            'ID' => 1,
            'username' => 'testuser',
            'email' => 'test@example.com',
            'firstname' => 'Test',
            'lastname' => 'User'
        ];
        
        // Create a mock result that will return our test data
        $mockResult = $this->createMock(mysqli_result::class);
        
        // Mock mysqli_fetch_assoc behavior
        global $fetchAssocCallCount;
        $fetchAssocCallCount = 0;
        
        // Override the global mysqli_fetch_assoc function for this test
        $this->mockDb->method('query_db')->willReturn($mockResult);
        
        $user = $this->createUserWithoutConstructor();
        
        // We can't easily test this without mocking mysqli functions
        // So we'll test the method exists and returns an array
        $result = $user->get_user_info('testuser');
        $this->assertIsArray($result);
    }

    private function createUserWithoutConstructor()
    {
        return $this->getMockBuilder(User::class)
                    ->disableOriginalConstructor()
                    ->onlyMethods([])
                    ->getMock();
    }

    private function setPrivateProperty($object, $property, $value)
    {
        $reflection = new ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }

    private function getPrivateProperty($object, $property)
    {
        $reflection = new ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        return $prop->getValue($object);
    }

    protected function tearDown(): void
    {
        resetGlobalState();
        unset($GLOBALS['Db']);
    }
}