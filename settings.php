<?php
define("DEBUG", 1) ;
//Alapkönyvtár
if (!defined("BASEDIR")) define("BASEDIR", __DIR__);
$inifile = BASEDIR . "/config/db.php" ;
$program = basename($_SERVER["PHP_SELF"]) ;
$base_url = $_SERVER["REQUEST_SCHEME"] . "://"
        . $_SERVER["HTTP_HOST"] . "/". basename(BASEDIR);

// Set debug screen
if (DEBUG) {
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
    @ini_set('display_errors', '1');
} else {
    // ne legyen hibauzenet
    error_reporting(0);
    @ini_set('display_errors', '0');
};

//Print debug info
if (file_exists("../debuglib.php")) include "../debuglib.php" ;

// Require the autoloader class file
require_once BASEDIR . '/classes/Autoloader.php';

// STATIC OPTION: register a static method with SPL
spl_autoload_register(array('Autoloader', 'loadStatic'));

//get ini file data - database settings
if (file_exists($inifile)) {
    include $inifile ;
}else{
    $errormsg = "inifile" ;
    return;
}

require_once(BASEDIR . '/classes/Validate.php');
require_once(BASEDIR . '/classes/Helper.php');
require_once(BASEDIR . '/classes/SessionConfig.php');

// Configure secure session settings before starting session
SessionConfig::configure();

$Db = new Db();

if (!Db::checkSetup()) {
    $errormsg = "setup" ;
    return false;
}

$user = new User($Db) ;
