<?php

// Define test environment constants
define("DEBUG", 0);
define("BASEDIR", dirname(__DIR__));

// Set up test database constants
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'test_small_login');
define('DB_USER', $_ENV['DB_USER'] ?? 'test_user');
define('DB_PASS', $_ENV['DB_PASS'] ?? 'test_pass');

// Suppress error reporting for cleaner test output
error_reporting(0);
ini_set('display_errors', '0');

// Start output buffering to capture any unwanted output
ob_start();

// Include the autoloader
require_once BASEDIR . '/classes/Autoloader.php';
spl_autoload_register(array('Autoloader', 'loadStatic'));

// Include required classes
require_once BASEDIR . '/classes/Validate.php';
require_once BASEDIR . '/classes/Helper.php';

// Mock global variables that the application expects
$GLOBALS['Db'] = null;
$GLOBALS['user'] = null;
$GLOBALS['errormsg'] = '';

// Helper function to reset global state between tests
function resetGlobalState() {
    $_SESSION = [];
    $_POST = [];
    $_GET = [];
    $_COOKIE = [];
    $GLOBALS['errormsg'] = '';
    
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    ob_start();
}

// Helper function to create test database connection
function createTestDbConnection() {
    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
    if (!$connection) {
        throw new Exception('Could not connect to test database: ' . mysqli_connect_error());
    }
    
    // Create test database if it doesn't exist
    $dbName = DB_NAME;
    mysqli_query($connection, "CREATE DATABASE IF NOT EXISTS `$dbName`");
    mysqli_select_db($connection, $dbName);
    
    return $connection;
}

// Helper function to clean up test database
function cleanupTestDatabase() {
    try {
        $connection = createTestDbConnection();
        mysqli_query($connection, "DROP TABLE IF EXISTS user");
        mysqli_close($connection);
    } catch (Exception $e) {
        // Ignore cleanup errors
    }
}