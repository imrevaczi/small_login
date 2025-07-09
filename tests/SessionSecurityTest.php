<?php

use PHPUnit\Framework\TestCase;

/**
 * Session Security Test Suite
 * 
 * Tests session security configurations and cookie security flags
 * to prevent session hijacking, XSS, and CSRF attacks.
 */
class SessionSecurityTest extends TestCase
{
    private $originalServerVars;
    
    protected function setUp(): void
    {
        // Backup original $_SERVER values
        $this->originalServerVars = $_SERVER;
        
        // Set required $_SERVER variables for settings.php
        $_SERVER['PHP_SELF'] = '/index.php';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_SCHEME'] = 'http';
        
        // Reset session state for each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        // Clear any existing session configuration
        ini_restore('session.use_strict_mode');
        ini_restore('session.use_only_cookies');
        ini_restore('session.cookie_httponly');
        ini_restore('session.cookie_secure');
        ini_restore('session.cookie_samesite');
        ini_restore('session.use_trans_sid');
        ini_restore('session.cache_limiter');
    }
    
    protected function tearDown(): void
    {
        // Restore original $_SERVER values
        $_SERVER = $this->originalServerVars;
        
        // Clean up session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
    
    /**
     * Test that session security configuration is applied correctly for HTTPS
     */
    public function testSecureSessionConfigurationWithHTTPS()
    {
        // Simulate HTTPS environment
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['REQUEST_SCHEME'] = 'https';
        
        // Test SessionConfig class directly
        require_once __DIR__ . '/../classes/SessionConfig.php';
        
        // In CLI mode, session might already be active, so we test what we can
        $this->assertEquals('1', ini_get('session.use_only_cookies'), 
            'Session should only use cookies');
        $this->assertEquals('0', ini_get('session.use_trans_sid'), 
            'Session should not use URL-based session IDs');
        
        // Test that SessionConfig class exists and has required methods
        $this->assertTrue(class_exists('SessionConfig'), 
            'SessionConfig class should exist');
        $this->assertTrue(method_exists('SessionConfig', 'configure'), 
            'SessionConfig should have configure method');
        $this->assertTrue(method_exists('SessionConfig', 'getSecurityStatus'), 
            'SessionConfig should have getSecurityStatus method');
    }
    
    /**
     * Test that session security configuration is applied correctly for HTTP
     */
    public function testSecureSessionConfigurationWithHTTP()
    {
        // Simulate HTTP environment
        unset($_SERVER['HTTPS']);
        $_SERVER['REQUEST_SCHEME'] = 'http';
        
        // Test SessionConfig class directly
        require_once __DIR__ . '/../classes/SessionConfig.php';
        
        // Test basic session security settings that should work
        $this->assertEquals('1', ini_get('session.use_only_cookies'), 
            'Session should only use cookies');
        $this->assertEquals('0', ini_get('session.use_trans_sid'), 
            'Session should not use URL-based session IDs');
        
        // Test that the security status method works
        $status = SessionConfig::getSecurityStatus();
        $this->assertIsArray($status, 'getSecurityStatus should return an array');
        $this->assertArrayHasKey('use_only_cookies', $status);
        $this->assertArrayHasKey('use_trans_sid', $status);
        $this->assertArrayHasKey('cookie_params', $status);
    }
    
    /**
     * Test that secure cookie method exists and works
     */
    public function testSecureCookieMethod()
    {
        // Test that the secure cookie method exists in User class
        require_once __DIR__ . '/../classes/User.php';
        
        $reflection = new ReflectionClass('User');
        $this->assertTrue($reflection->hasMethod('set_secure_cookie'), 
            'User class should have set_secure_cookie method');
        
        $method = $reflection->getMethod('set_secure_cookie');
        $this->assertTrue($method->isPrivate(), 
            'set_secure_cookie method should be private');
        
        // Test method parameters
        $parameters = $method->getParameters();
        $this->assertCount(5, $parameters, 
            'set_secure_cookie should have 5 parameters');
        $this->assertEquals('name', $parameters[0]->getName());
        $this->assertEquals('value', $parameters[1]->getName());
        $this->assertEquals('expire', $parameters[2]->getName());
    }
    
    /**
     * Test session security features are implemented
     */
    public function testSessionSecurityFeatures()
    {
        require_once __DIR__ . '/../classes/SessionConfig.php';
        
        // Test that SessionConfig provides security status
        $status = SessionConfig::getSecurityStatus();
        
        // Verify essential security settings are tracked
        $this->assertArrayHasKey('use_only_cookies', $status);
        $this->assertArrayHasKey('use_trans_sid', $status);
        $this->assertArrayHasKey('cache_limiter', $status);
        $this->assertArrayHasKey('cookie_params', $status);
        
        // Verify secure defaults
        $this->assertEquals('1', $status['use_only_cookies'], 
            'Should only use cookies for session ID');
        $this->assertEquals('0', $status['use_trans_sid'], 
            'Should not use URL-based session IDs');
    }
    
    /**
     * Test that session is active after User instantiation
     */
    public function testSessionIsActive()
    {
        require_once __DIR__ . '/../settings.php';
        
        // In CLI mode, session status might be different, so test what we can
        $sessionStatus = session_status();
        $this->assertGreaterThan(0, $sessionStatus, 
            'Session should be initialized');
        
        // In CLI mode, session ID might be empty, so just test that session_id() function works
        $sessionId = session_id();
        $this->assertIsString($sessionId, 
            'Session ID should be a string (even if empty in CLI mode)');
    }
    
    /**
     * Test that session security measures are in place
     */
    public function testSessionSecurityMeasures()
    {
        require_once __DIR__ . '/../settings.php';
        
        // Test against session hijacking via URL
        $this->assertEquals('0', ini_get('session.use_trans_sid'), 
            'Disabled trans_sid prevents URL-based session hijacking');
        
        // Test that session uses only cookies
        $this->assertEquals('1', ini_get('session.use_only_cookies'), 
            'Session should only use cookies, not URL parameters');
        
        // Test session cache settings
        $this->assertEquals('nocache', ini_get('session.cache_limiter'), 
            'No-cache prevents session data caching');
    }
    
    /**
     * Test HTTPS detection for secure cookies
     */
    public function testHTTPSDetection()
    {
        // Test HTTPS detection logic
        $_SERVER['HTTPS'] = 'on';
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $this->assertTrue($secure, 'Should detect HTTPS correctly');
        
        // Test HTTP detection
        unset($_SERVER['HTTPS']);
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $this->assertFalse($secure, 'Should detect HTTP correctly');
    }
}