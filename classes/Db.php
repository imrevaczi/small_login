<?php

class Db {

    public $version = '0.9';
    protected $dbhost = "undefined";
    protected $dbuser = "nobody";
    protected $dbuserpass = "nopasswd";
    protected $database = "none";   
    private $error = [] ;

    public function __construct() {
        $this->dbhost = DB_HOST;
        $this->dbuser = DB_USER;
        $this->dbuserpass = DB_PASS;
        $this->database = DB_NAME;
    }

    public function __toString() {
        return " Version: " . $this->version;
    }

    public function connect() {
        global $Db ; 
        $kapcsolat = mysqli_connect($Db->dbhost, $Db->dbuser, $Db->dbuserpass);
        if (!$kapcsolat) {
            if (DEBUG) {
                echo '<br />Database connection failed.<br>' . mysqli_connect_error();
            };
            return false;
        }
        $success = mysqli_select_db($kapcsolat, $Db->database);
        if (!$success) {
            if (!Db::createDatabase($kapcsolat, $Db->database)) {
                if (DEBUG)
                    echo '<br />Selecting database not succeeded<br>' . mysqli_error($mysql);
                return false;
            }else {
                if (DEBUG)
                    $Db->error[] = 'Creating database... ready.';
            }
        }

        if (!mysqli_set_charset($kapcsolat, "utf8")) {
            if (DEBUG) {
                echo '<br />Invalid query: ' . mysqli_error($kapcsolat);
            }
            return false;
        }

        return $kapcsolat;
    }

        // Show error messages
    public function display_errors() {
        if (count($this->error)>0) {
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

    // Query database
    public static function query_db($query, $showerror = true, $multi = "") {
        global $Db;
        $showerror = $showerror ? DEBUG : false;
        $mysql = $Db->connect();
        if (!$mysql) {
            die(__FUNCTION__ . " connection error.");
            return false;
        }

        if ($multi == "multi") {
            //without result set
            $result = mysqli_multi_query($mysql, $query);
        } else {
            $result = mysqli_query($mysql, $query);
        }

        if (!$result) {
            if ($showerror) {
                echo "<br />Query error: $query<br>" . mysqli_errno($mysql) . ":" . mysqli_error($mysql);
            };
        } elseif (is_bool($result) && $result === true && strstr($query, "INSERT INTO")) {
            $result = mysqli_insert_id($mysql);
        }
        mysqli_close($mysql);
        return $result;
    }

    // Secure prepared statement method
    public static function prepare_and_execute($query, $types = "", $params = []) {
        global $Db;
        $mysql = $Db->connect();
        if (!$mysql) {
            if (DEBUG) {
                echo "<br />Connection error in prepared statement.";
            }
            return false;
        }

        $stmt = mysqli_prepare($mysql, $query);
        if (!$stmt) {
            if (DEBUG) {
                echo "<br />Prepare failed: " . mysqli_error($mysql);
            }
            mysqli_close($mysql);
            return false;
        }

        // Bind parameters if provided
        if (!empty($types) && !empty($params)) {
            if (!mysqli_stmt_bind_param($stmt, $types, ...$params)) {
                if (DEBUG) {
                    echo "<br />Binding parameters failed: " . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
                mysqli_close($mysql);
                return false;
            }
        }

        // Execute the statement
        if (!mysqli_stmt_execute($stmt)) {
            if (DEBUG) {
                echo "<br />Execute failed: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
            mysqli_close($mysql);
            return false;
        }

        // Get the result
        $result = mysqli_stmt_get_result($stmt);
        
        // For INSERT statements, return the insert ID
        if ($result === false && strpos(strtoupper($query), 'INSERT') === 0) {
            $insert_id = mysqli_insert_id($mysql);
            mysqli_stmt_close($stmt);
            mysqli_close($mysql);
            return $insert_id;
        }

        // For other statements that don't return a result set
        if ($result === false) {
            $affected_rows = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            mysqli_close($mysql);
            return $affected_rows > 0;
        }

        mysqli_stmt_close($stmt);
        mysqli_close($mysql);
        return $result;
    }

    public static function createDatabase($kapcsolat, $database) {
        $query = 'CREATE DATABASE ' . $database . ' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;';
        $result = mysqli_query($kapcsolat, $query);
        return $result;
    }

    public static function checkSetup() {
        global $Db ;
        $query = "SELECT * FROM user";
        $res = Db::query_db($query, FALSE);
        if ($res) {
            return true;
        } else {
            $sql = 'CREATE TABLE `user` (
                `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `username` varchar(100) NOT NULL,
                `firstname` varchar(100) NOT NULL,
                `lastname` varchar(100) NOT NULL,
                email varchar(100) NOT NULL,
                mobile varchar(100) NOT NULL,
                password varchar(100) NOT NULL
              ) ENGINE=MyISAM DEFAULT CHARSET=utf8;';

            $res = Db::query_db($sql, true, "");
            if ($res) {
                if (DEBUG)
                    $Db->error[] = 'Creating user table... ready';
                return true;
            }
        }
        return false;
    }

    public static function getUserFields($table) {
        $sql = "SHOW COLUMNS FROM user";
        $result = Db::query_db($sql);
        $cols = [];
        while ($sor = mysqli_fetch_assoc($result)) {
            if ($sor["Field"] !== "ID") {
                $cols[] = $sor;
            }
        }
        mysqli_free_result($result);
        return $cols;
    }

    
}
