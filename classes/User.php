<?php

class User {

    private $email;
    private $is_logged_in = false;
    private $username;
    private $db ;
    private $error = [] ;

    // Create a new user object
    public function __construct($Db) {
       
        session_start();

        if (isset($_GET['logout'])) {
            $this->logout();
            
        } elseif (isset($_COOKIE['username']) || (!empty($_SESSION['username']) && $_SESSION['is_logged_in'])) {
            $this->is_logged_in = true;
            $this->username = $_SESSION['username'];
                
            if (isset($_POST['register'])) {
                $this->register();                
            }
        } elseif (isset($_POST['login'])) {
            $this->login();
            
        } elseif ($this->empty_db() && isset($_POST['register'])) {
            $this->register();
            
        }

        return $this;
    }

    // Get username

    public function get_username() {
        return $this->username;
    }

    // Get email

    public function get_email() {
        return $this->email;
    }

    // Check if the user is logged

    public function is_logged_in() {
        return $this->is_logged_in;
    }

    // Login

    public function login() {

        if (!empty($_POST['username']) && !empty($_POST['password'])) {

            $this->username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $this->password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

            if ($row = $this->verify_password()) {
                session_regenerate_id(true);
                $_SESSION['id'] = session_id();
                $_SESSION['username'] = $this->username;
                $_SESSION['is_logged_in'] = true;
                $this->is_logged_in = true;
                // Set a cookie that expires in one week
                if (isset($_POST['remember']))
                    setcookie('username', $this->username, time() + 604800);
                // To avoid resending the form on refreshing
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit();
            } else
                $this->error[] = 'Wrong user or password.';
        } elseif (empty($_POST['username'])) {

            $this->error[] = 'Username field was empty.';
        } elseif (empty($_POST['password'])) {

            $this->error[] = 'Password field was empty.';
        }
    }

    // Check if username and password match

    private function verify_password() {

        $query = 'SELECT * FROM user '
                . 'WHERE username = "' . $this->username . '" ' ;
        $result = Db::query_db($query);
        $user = false;
        while ($sor = mysqli_fetch_assoc($result)) {
            $user = $sor;
        }
        mysqli_free_result($result);
        if ($user) {
            if (password_verify($this->password, $user["password"])):
                return $user ;
            endif;
        }
        return FALSE ;
    }

    // Logout function

    public function logout() {

        session_unset();
        session_destroy();
        $this->is_logged_in = false;
        setcookie('username', '', time() - 3600);
        header('Location: index.php');
        exit();
    }

    // Get post data
    public function get_post() {
        $post = [] ;
        $fields = Db::getUserFields() ;
        foreach ($fields as $fld) {
            switch ($fld["Field"]) {
                case "email":
                    $post[$fld["Field"]] = filter_input(INPUT_POST, $fld["Field"], FILTER_SANITIZE_EMAIL) ;
                    break;
                default :
                    $post[$fld["Field"]] = filter_input(INPUT_POST, $fld["Field"], FILTER_SANITIZE_STRING) ;                    
            }
        }
        $post["confirm"] = filter_input(INPUT_POST, "confirm", FILTER_SANITIZE_STRING) ;                           
        return $post ;
    }
    
    // Register a new user
    public function register() {
        
        $post = $this->get_post() ;

        if (!empty($post['username']) && !empty($post['password']) && !empty($post['confirm'])) {

            if ($post['password'] == $post['confirm']) {

                $first_user = $this->empty_db();
                $post["password"] = password_hash($post['password'], PASSWORD_DEFAULT);
                unset($post["confirm"]) ;
                $field_string = join(",", array_keys($post)) ;
                $field_values = '"'.join('","', $post).'"' ;

                $query = 'INSERT INTO user ('.$field_string.') '
                        . 'VALUES ('.$field_values.')';
                if (Db::query_db($query)) {

                    if ($first_user) {
                        session_regenerate_id(true);
                        $_SESSION['id'] = session_id();
                        $_SESSION['username'] = $post["username"];
                        $_SESSION['is_logged_in'] = true;
                        $this->is_logged_in = true;
                    } else {
                        $this->msg[] = 'User created.';
                        $_SESSION['msg'] = $this->msg;
                    }
                    // To avoid resending the form on refreshing
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit();
                } else
                    $this->error[] = 'Username already exists.';
            } else
                $this->error[] = 'Passwords don\'t match.';
            
        } 
        if (empty($post['username'])) {
            $this->error[] = 'Username field was empty.';
            
        } 
        if (empty($post['email'])) {
            $this->error[] = 'Email field was empty.';
            
        } 
        if (empty($post['password'])) {
            $this->error[] = 'Password field was empty.';
            
        } 
        if (empty($post['confirm'])) {
            $this->error[] = 'You need to repeat the password.';
        }
    }

    // Get info about an user
    public function get_user_info($username) {
        $query = 'SELECT * FROM user WHERE username = "' . $username . '" LIMIT 1';
        $result = Db::query_db($query);
        $userinfo = [] ;
        while ($sor = mysqli_fetch_assoc($result)) {
            $userinfo = $sor;
        }
        mysqli_free_result($result);
        return $userinfo ;        
    }
    
    // Show error messages
    public function display_errors() {
        if ($this->count_errors()>0) {
            $errormsg = "" ;
            foreach ($this->error as $err) {
                $errormsg .= $err ."<br />" ;
            }
            return $errormsg ;
        }
        return "" ;
    }
    
    //Count errors
    public function count_errors() {
        return count($this->error) ;
    }

    // Check if the user db has any user
    public function empty_db() {
        $query = 'SELECT * FROM user';
        $result = Db::query_db($query);
        return ($result->num_rows === 0);
    }

}

?>