<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../classes/Validate.php';

use PHPUnit\Framework\TestCase;

/**
 * Test to verify that the deprecated ereg() function has been successfully
 * replaced with preg_match() and that email validation works correctly
 */
class EregFixTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset global state if needed
        global $jog;
        $jog = null;
        $_SESSION = [];
    }

    public function testEregFunctionIsNotUsedInCode()
    {
        $validateCode = file_get_contents(__DIR__ . '/../classes/Validate.php');
        
        // Verify ereg() is not used anywhere
        $this->assertStringNotContainsString('ereg(', $validateCode, 
            'ereg() function should not be present in the code');
        
        // Verify preg_match() is used instead
        $this->assertStringContainsString('preg_match(', $validateCode,
            'preg_match() function should be used instead of ereg()');
    }

    public function testEmailValidationWithValidEmails()
    {
        $validEmails = [
            'test@example.com',
            'user.name@domain.co.uk',
            'user_name@domain.org',
            'user-name@domain.net',
            'test123@test.info',
            'a@b.co'
        ];

        foreach ($validEmails as $email) {
            $this->assertTrue(Validate::valid_email($email), 
                "Email '$email' should be valid");
        }
    }

    public function testEmailValidationWithInvalidEmails()
    {
        $invalidEmails = [
            'invalid-email',           // no @ or domain
            '@domain.com',            // no local part
            'user@',                  // no domain
            'user@domain',            // no TLD
            '',                       // empty
            'user space@domain.com',  // space not allowed
            'user@domain..com',       // consecutive dots
            'user.@domain.com',       // dot before @
            'user@.domain.com',       // dot after @
            'user@@domain.com',       // multiple @
            'user@domain@com',        // multiple @
        ];

        foreach ($invalidEmails as $email) {
            $this->assertFalse(Validate::valid_email($email), 
                "Email '$email' should be invalid");
        }
    }

    public function testEmailValidationIsPhp7Compatible()
    {
        // This test will fail if ereg() is still being used in PHP 7+
        $testEmail = 'test@example.com';
        
        // This should not throw any "Call to undefined function ereg()" errors
        $result = Validate::valid_email($testEmail);
        $this->assertTrue($result);
        
        // Test with invalid email
        $invalidEmail = 'invalid-email';
        $result = Validate::valid_email($invalidEmail);
        $this->assertFalse($result);
    }

    public function testImprovedEmailValidationLogic()
    {
        // Test cases that demonstrate improved validation over simple ereg
        
        // Should reject consecutive dots
        $this->assertFalse(Validate::valid_email('user@domain..com'));
        
        // Should reject dot immediately before @
        $this->assertFalse(Validate::valid_email('user.@domain.com'));
        
        // Should reject dot immediately after @
        $this->assertFalse(Validate::valid_email('user@.domain.com'));
        
        // Should reject multiple @ symbols
        $this->assertFalse(Validate::valid_email('user@@domain.com'));
        $this->assertFalse(Validate::valid_email('user@domain@com'));
        
        // Should accept valid emails with dots and underscores
        $this->assertTrue(Validate::valid_email('user.name@domain.com'));
        $this->assertTrue(Validate::valid_email('user_name@domain.com'));
    }

    public function testCheckPermissionDoesNotThrowErrors()
    {
        // This should not throw any errors even when global variables are not set
        $result = Validate::checkPermission('some_permission');
        $this->assertFalse($result);
        
        // Test with session but no global $jog
        $_SESSION['user_access'] = [1, 2, 3];
        $result = Validate::checkPermission('some_permission');
        $this->assertFalse($result);
    }

    protected function tearDown(): void
    {
        // Reset global state if needed
    }
}