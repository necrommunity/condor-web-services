<?php
$password = NULL;

if(isset($_GET['pass'])) {
    $password = $_GET['pass'];
}

$salt = NULL;

if(isset($_GET['salt'])) {
    $salt = $_GET['salt'];
}

// If either username or key is empty then return an error
if(!(!empty($password) && !empty($salt))) {
    if(!empty($_REQUEST['req'])) {
        $password_salt = $_REQUEST['req'];
        
        $password_name_key_split = explode('/', $password_salt);
        
        if(count($password_name_key_split) == 2) {
            $password = $password_name_key_split[0];
            $salt = $password_name_key_split[1];
        }
    }

    if(!(!empty($password) && !empty($salt))) {
        //echo '<pre>' . "Please provide both a password and salt" . '</pre>'
	header("HTTP/1.1 404 Not Found");
        exit;
    }
}

// If either username or key is empty then return an error
echo '<pre>' . password_hash("{$salt}_{$password}", PASSWORD_DEFAULT) . '</pre>';
