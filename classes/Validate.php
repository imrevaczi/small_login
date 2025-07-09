<?php

class Validate {

    public static function valid_email($email) {
        // Convert ereg to preg_match with PCRE syntax and improve validation
        // First check: basic email structure (something@something.something)
        // Second check: only allowed characters
        // Third check: no consecutive dots or other invalid patterns
        if (empty($email)) {
            return false;
        }
        
        // Basic structure check: must have @ and at least one dot after @
        if (!preg_match("/.+@.+\..+/", $email)) {
            return false;
        }
        
        // Character validation: only allow letters, numbers, underscore, @, dot, and hyphen
        if (!preg_match("/^[a-zA-Z0-9_@.-]+$/", $email)) {
            return false;
        }
        
        // Additional validation: no consecutive dots, no dot before @, etc.
        if (preg_match("/\.\./", $email) || preg_match("/\.@/", $email) || preg_match("/@\./", $email)) {
            return false;
        }
        
        // Must have exactly one @ symbol
        if (substr_count($email, '@') !== 1) {
            return false;
        }
        
        return true;
    }

    // jogosultsagi nevnek megfelelo jogosultsag ellenorzese a SESSION ALAPJAN
    public static function checkPermission($perm_name) {
        global $jog;
        // eloszor megnezzuk, hogy van-e ilyen kulcs a jogosultsag tombben, masreszt van-e jogosultsag a sessionben
        if (isset($jog) && is_array($jog) && array_key_exists($perm_name, $jog) && isset($_SESSION['user_access']) && is_array($_SESSION['user_access'])) {
            $perm_code = (int) $jog[$perm_name];
            // a jogosultsagkod  alapjan ellenorizzuk a jogosultsagot
            return in_array($perm_code, $_SESSION['user_access']);
        } else {
            return false;
        };
    }

    public static function checkPassword($pwd, &$errors) {
        $errors_init = $errors = array();

        if (strlen($pwd) < 8) {
            $errors[] = "A jelszó túl rövid! Legalább 8 karakter legyen.";
        }

        if (!preg_match("#[0-9]+#", $pwd)) {
            $errors[] = "A jelszónak legalább egy számot kell tartalmaznia!";
        }

        if (!preg_match("#[a-zA-Z]+#", $pwd)) {
            $errors[] = "A jelszónak legalább egy betűt kell tartalmaznia!";
        }

        return ($errors == $errors_init);
    }

}
