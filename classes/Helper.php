<?php

class Helper {

    public static function getTitle($template) {
        switch ($template) {
            case "register":
                $title = "Register a New User" ;
                break ;
            case "login":
                $title = "Login" ;
                break ;
            default :
                $title = "Show User Data" ;
                break ;
        }
        return $title;
    }
    
    // -------------------------------------------------------------------------------------------------
    // Redirect by relative url
    // -------------------------------------------------------------------------------------------------
    public static function loc_redirect($relative_url) {
        header("Location: ".$_SERVER["REQUEST_SCHEME"] . '//' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/" . $relative_url);
        $_SESSION['relative_url'] = $relative_url;
        exit;
    }

    public static function getErrorMessage() {
        global $errormsg, $user, $Db ;
        // Based on $_GET['errormsg'] send back error messages
        if (empty($errormsg))
            $errormsg = filter_input(INPUT_GET, "errormsg", FILTER_SANITIZE_STRING);
        
        switch ($errormsg) {
            case 'inifile':
                $loginerror = 'INI file not found';
                break;
            case 'failed':
                $loginerror = 'Login failed. <br>Maybe you typed in wrong user name or password';
                break;
            case 'setup':
                $loginerror = 'Setup error';
                break;
            case 'cookie':
                $loginerror = 'You should allow to accept COOKIE data!';
                break;
            default:
                $loginerror = '';
        }
        
        if ($Db->count_errors() > 0) {
            $loginerror .= empty($loginerror) ? "" : "<br />" ;
            $loginerror .= $Db->display_errors() ;
        }        
        if ($user->count_errors()>0) {
            $loginerror .= empty($loginerror) ? "" : "<br />" ;
            $loginerror .= $user->display_errors() ;
        }        
        
        return $loginerror ;
    }
    
}
