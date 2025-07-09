<?php

use PHPUnit\Framework\TestCase;

/**
 * Simple demonstration test to show the testing framework works
 */
class SimpleTest extends TestCase
{
    public function testBasicPHPFunctionality()
    {
        $this->assertEquals(4, 2 + 2);
        $this->assertTrue(is_string('hello'));
        $this->assertFalse(empty('test'));
    }

    public function testPasswordHashing()
    {
        $password = 'TestPassword123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $this->assertNotEquals($password, $hash);
        $this->assertTrue(password_verify($password, $hash));
        $this->assertFalse(password_verify('wrong', $hash));
    }

    public function testHelperGetTitle()
    {
        $this->assertEquals('Register a New User', Helper::getTitle('register'));
        $this->assertEquals('Login', Helper::getTitle('login'));
        $this->assertEquals('Show User Data', Helper::getTitle('unknown'));
    }

    public function testValidatePasswordStrength()
    {
        $errors = [];
        
        // Test strong password
        $result = Validate::checkPassword('StrongPass123', $errors);
        $this->assertTrue($result);
        $this->assertEmpty($errors);
        
        // Test weak password
        $errors = [];
        $result = Validate::checkPassword('weak', $errors);
        $this->assertFalse($result);
        $this->assertNotEmpty($errors);
    }

    public function testDbClassExists()
    {
        $db = new Db();
        $this->assertInstanceOf(Db::class, $db);
        $this->assertStringContainsString('Version: 0.9', (string) $db);
    }
}