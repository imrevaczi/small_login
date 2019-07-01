<?php

class Validate {

    public static function valid_email($email) {
        if ((!ereg(".+\@.+\..+", $email)) || (!ereg("^[a-zA-Z0-9_@.-]+$", $email)))
            return false;
        else
            return true;
    }

    // jogosultsagi nevnek megfelelo jogosultsag ellenorzese a SESSION ALAPJAN
    public static function checkPermission($perm_name) {
        global $jog;
        // eloszor megnezzuk, hogy van-e ilyen kulcs a jogosultsag tombben, masreszt van-e jogosultsag a sessionben
        if (array_key_exists($perm_name, $jog) && is_array($_SESSION['user_access'])) {
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
