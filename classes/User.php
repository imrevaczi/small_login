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

    // Login - ENHANCED with better input validation

    public function login() {

        if (!empty($_POST['username']) && !empty($_POST['password'])) {

            // Enhanced input sanitization for login
            $rawUsername = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
            $rawPassword = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
            
            // Username: only allow alphanumeric, underscore, and hyphen
            $this->username = preg_replace('/[^a-zA-Z0-9_-]/', '', $rawUsername);
            // Password: keep as-is but ensure it's a string and limit length
            $this->password = is_string($rawPassword) ? substr($rawPassword, 0, 255) : '';

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

    // Check if username and password match - SECURE VERSION with prepared statements

    private function verify_password() {
        // Use prepared statement to prevent SQL injection
        $query = 'SELECT * FROM user WHERE username = ? LIMIT 1';
        $result = Db::prepare_and_execute($query, "s", [$this->username]);
        
        if (!$result) {
            return false;
        }
        
        $user = false;
        while ($row = mysqli_fetch_assoc($result)) {
            $user = $row;
        }
        mysqli_free_result($result);
        
        if ($user && password_verify($this->password, $user["password"])) {
            return $user;
        }
        
        return false;
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

    // Get post data - ENHANCED with better input validation and sanitization
    public function get_post() {
        $post = [];
        $fields = Db::getUserFields();
        
        foreach ($fields as $fld) {
            $fieldName = $fld["Field"];
            $rawValue = filter_input(INPUT_POST, $fieldName, FILTER_UNSAFE_RAW);
            
            switch ($fieldName) {
                case "email":
                    // Enhanced email validation and sanitization
                    $post[$fieldName] = filter_var($rawValue, FILTER_SANITIZE_EMAIL);
                    break;
                case "username":
                    // Username: only allow alphanumeric, underscore, and hyphen
                    $post[$fieldName] = preg_replace('/[^a-zA-Z0-9_-]/', '', $rawValue);
                    break;
                case "firstname":
                case "lastname":
                    // Names: allow letters, spaces, apostrophes, and hyphens
                    $post[$fieldName] = preg_replace('/[^a-zA-Z\s\'-]/', '', $rawValue);
                    break;
                case "mobile":
                    // Mobile: only allow numbers, spaces, +, -, (, )
                    $post[$fieldName] = preg_replace('/[^0-9\s\+\-\(\)]/', '', $rawValue);
                    break;
                case "password":
                    // Password: keep as-is but ensure it's a string
                    $post[$fieldName] = is_string($rawValue) ? $rawValue : '';
                    break;
                default:
                    // Default: basic sanitization
                    $post[$fieldName] = htmlspecialchars(strip_tags($rawValue), ENT_QUOTES, 'UTF-8');
            }
            
            // Additional length validation
            if (strlen($post[$fieldName]) > 100) {
                $post[$fieldName] = substr($post[$fieldName], 0, 100);
            }
        }
        
        // Handle confirm password field
        $confirmValue = filter_input(INPUT_POST, "confirm", FILTER_UNSAFE_RAW);
        $post["confirm"] = is_string($confirmValue) ? $confirmValue : '';
        
        return $post;
    }
    
    // Register a new user - SECURE VERSION with prepared statements
    public function register() {
        
        $post = $this->get_post();

        if (!empty($post['username']) && !empty($post['password']) && !empty($post['confirm'])) {

            if ($post['password'] == $post['confirm']) {

                $first_user = $this->empty_db();
                $post["password"] = password_hash($post['password'], PASSWORD_DEFAULT);
                unset($post["confirm"]);
                
                // Build secure prepared statement for INSERT
                $fields = array_keys($post);
                $placeholders = str_repeat('?,', count($fields) - 1) . '?';
                $field_string = implode(',', $fields);
                
                $query = 'INSERT INTO user (' . $field_string . ') VALUES (' . $placeholders . ')';
                
                // Create type string (all strings for user data)
                $types = str_repeat('s', count($post));
                $values = array_values($post);
                
                $result = Db::prepare_and_execute($query, $types, $values);
                
                if ($result) {
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
                } else {
                    $this->error[] = 'Username already exists.';
                }
            } else {
                $this->error[] = 'Passwords don\'t match.';
            }
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

    // Get info about an user - SECURE VERSION with prepared statements
    public function get_user_info($username) {
        // Use prepared statement to prevent SQL injection
        $query = 'SELECT * FROM user WHERE username = ? LIMIT 1';
        $result = Db::prepare_and_execute($query, "s", [$username]);
        
        if (!$result) {
            return [];
        }
        
        $userinfo = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $userinfo = $row;
        }
        mysqli_free_result($result);
        return $userinfo;
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