<?php

use PHPUnit\Framework\TestCase;

class ValidateTest extends TestCase
{
    protected function setUp(): void
    {
        resetGlobalState();
    }

    public function testValidEmailWithValidEmails()
    {
        $validEmails = [
            'test@example.com',
            'user.name@domain.co.uk',
            'test123@test-domain.com',
            'user_name@example.org'
        ];

        foreach ($validEmails as $email) {
            $this->assertTrue(
                Validate::valid_email($email),
                "Email '$email' should be valid"
            );
        }
    }

    public function testValidEmailWithInvalidEmails()
    {
        $invalidEmails = [
            'invalid-email',
            '@domain.com',
            'user@',
            'user@domain',
            'user name@domain.com', // space not allowed
            'user@domain..com',
            ''
        ];

        foreach ($invalidEmails as $email) {
            $this->assertFalse(
                Validate::valid_email($email),
                "Email '$email' should be invalid"
            );
        }
    }

    public function testCheckPasswordWithValidPasswords()
    {
        $validPasswords = [
            'password123',
            'MyPass123',
            'test1234',
            'SecurePass1'
        ];

        foreach ($validPasswords as $password) {
            $errors = [];
            $result = Validate::checkPassword($password, $errors);
            
            $this->assertTrue($result, "Password '$password' should be valid");
            $this->assertEmpty($errors, "No errors should be present for valid password");
        }
    }

    public function testCheckPasswordWithShortPassword()
    {
        $errors = [];
        $result = Validate::checkPassword('short1', $errors);
        
        $this->assertFalse($result);
        $this->assertContains('A jelszó túl rövid! Legalább 8 karakter legyen.', $errors);
    }

    public function testCheckPasswordWithoutNumbers()
    {
        $errors = [];
        $result = Validate::checkPassword('passwordonly', $errors);
        
        $this->assertFalse($result);
        $this->assertContains('A jelszónak legalább egy számot kell tartalmaznia!', $errors);
    }

    public function testCheckPasswordWithoutLetters()
    {
        $errors = [];
        $result = Validate::checkPassword('12345678', $errors);
        
        $this->assertFalse($result);
        $this->assertContains('A jelszónak legalább egy betűt kell tartalmaznia!', $errors);
    }

    public function testCheckPasswordWithMultipleErrors()
    {
        $errors = [];
        $result = Validate::checkPassword('123', $errors);
        
        $this->assertFalse($result);
        $this->assertCount(2, $errors); // Should have both "too short" and "needs letters" errors
    }

    public function testCheckPermissionWithoutSession()
    {
        // Test when no session is set
        $result = Validate::checkPermission('some_permission');
        $this->assertFalse($result);
    }

    public function testCheckPermissionWithInvalidPermission()
    {
        // Mock global $jog array
        $GLOBALS['jog'] = ['valid_perm' => 1];
        $_SESSION['user_access'] = [1, 2, 3];
        
        $result = Validate::checkPermission('invalid_perm');
        $this->assertFalse($result);
    }

    public function testCheckPermissionWithValidPermission()
    {
        // Mock global $jog array
        $GLOBALS['jog'] = ['test_permission' => 2];
        $_SESSION['user_access'] = [1, 2, 3];
        
        $result = Validate::checkPermission('test_permission');
        $this->assertTrue($result);
    }

    public function testCheckPermissionWithoutAccess()
    {
        // Mock global $jog array
        $GLOBALS['jog'] = ['test_permission' => 5];
        $_SESSION['user_access'] = [1, 2, 3];
        
        $result = Validate::checkPermission('test_permission');
        $this->assertFalse($result);
    }

    protected function tearDown(): void
    {
        // Clean up global variables
        unset($GLOBALS['jog']);
        resetGlobalState();
    }
}