<?php

/**
 * Session Security Configuration
 * 
 * This class handles secure session configuration that must be set
 * before session_start() is called.
 */
class SessionConfig
{
    /**
     * Configure secure session settings
     * Must be called before session_start()
     */
    public static function configure()
    {
        // Only configure if session hasn't started yet
        if (session_status() === PHP_SESSION_NONE) {
            // Determine if we're using HTTPS
            $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            
            try {
                // Set session security settings BEFORE session_start()
                ini_set('session.use_strict_mode', '1');        // Reject uninitialized session IDs
                ini_set('session.use_only_cookies', '1');       // Only use cookies for session ID
                ini_set('session.cookie_httponly', '1');        // HttpOnly flag
                ini_set('session.cookie_secure', $secure ? '1' : '0'); // Secure flag if HTTPS
                ini_set('session.cookie_samesite', 'Strict');   // SameSite protection
                ini_set('session.use_trans_sid', '0');          // Don't use URL-based sessions
                ini_set('session.cache_limiter', 'nocache');    // Prevent caching of session pages
                
                // Set secure session cookie parameters
                session_set_cookie_params([
                    'lifetime' => 0,           // Session cookie (expires when browser closes)
                    'path' => '/',             // Available for entire domain
                    'domain' => '',            // Current domain only
                    'secure' => $secure,       // Only send over HTTPS if available
                    'httponly' => true,        // Not accessible via JavaScript
                    'samesite' => 'Strict'     // CSRF protection
                ]);
            } catch (Exception $e) {
                // Session configuration failed, but continue
                error_log("Session configuration warning: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Get current session security status
     * 
     * @return array Session security configuration
     */
    public static function getSecurityStatus()
    {
        return [
            'use_strict_mode' => ini_get('session.use_strict_mode'),
            'use_only_cookies' => ini_get('session.use_only_cookies'),
            'cookie_httponly' => ini_get('session.cookie_httponly'),
            'cookie_secure' => ini_get('session.cookie_secure'),
            'cookie_samesite' => ini_get('session.cookie_samesite'),
            'use_trans_sid' => ini_get('session.use_trans_sid'),
            'cache_limiter' => ini_get('session.cache_limiter'),
            'cookie_params' => session_get_cookie_params()
        ];
    }
}