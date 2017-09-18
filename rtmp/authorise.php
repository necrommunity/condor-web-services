<?php
require_once(dirname(__FILE__) . '/php/config.php');

/* ---------- Exception handler for sending 500 headers if something goes wrong ---------- */

function handle_throwable($throwable) {    
    $previous_throwable = $throwable->getPrevious();
    
    $error_message = '';
    
    if(!empty($previous_throwable)) {
        $error_message = "===== RTMP Authentication Error ===== | " . 
            "Code: {$previous_throwable->getCode()} | " . 
            "Message: {$previous_throwable->getMessage()} | " . 
            "File: {$previous_throwable->getFile()} | " . 
            "Line: {$previous_throwable->getLine()} | " . 
            "Trace: $previous_throwable->getTraceAsString() " . 
            "=====";
    }
    else {
        $error_message = "===== RTMP Authentication Error ===== | " . 
            "Code: {$throwable->getCode()} | " . 
            "Message: {$throwable->getMessage()} | " . 
            "File: {$throwable->getFile()} | " . 
            "Line: {$throwable->getLine()} | " . 
            "Trace: $throwable->getTraceAsString() " . 
            "=====";
    }
    
    error_log($error_message);

    header('HTTP/1.1 500 Internal Server Error');
    
    exit;
}

set_exception_handler('handle_throwable');

/* ---------- Request Variables ---------- */

$username = NULL;

if(isset($_REQUEST['username'])) {
    $username = $_REQUEST['username'];
}

$app = NULL;

if(isset($_REQUEST['app'])) {
    $app = $_REQUEST['app'];
}

$password = NULL;

if(isset($_REQUEST['pass'])) {
    $password = $_REQUEST['pass'];
}

// If either username or password is empty then return an error
if(!(!empty($username) && !empty($password) && !empty($app))) {
   header("HTTP/1.1 500 Internal Server Error");
    exit;
}

if(stripos($app, $username) === false) {
    header("HTTP/1.1 500 Internal Server Error");
	echo "Username is not contained in app.";

    exit;
}

/* ---------- Database Connection and Query ---------- */

$connection_string = 'mysql:host=' . DBHOST . ';dbname=' . DBNAME;

$database = new PDO($connection_string, DBUSER, DBPASS, array(
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
));

$statement_object = $database->prepare("
    SELECT *
    FROM " . DBTABLE . "
    WHERE username = :username
    AND archived = 0
");

$statement_object->execute(array(
    ':username' => $username
));

$result_row = $statement_object->fetch(PDO::FETCH_ASSOC);

/* ---------- Authentication Logic ---------- */

// If a row exists for this user then proceed
if(!empty($result_row)) {
    // If this user's password matches what was in the request then send a 202 header
    if(password_verify("{$result_row['salt']}_{$password}", $result_row['password'])) {
        header("HTTP/1.1 202 Accepted"); // 2xx responses will keep session going
		echo "Accepted";
    }

    // If this user's password doesn't match what was in the request then send a 403 header
    else {
	header("HTTP/1.1 500 Internal Server Error");
	echo "Wrong username.";
    }
}
// If a row doesn't exist for this user, throw a forbidden header
else {
    header("HTTP/1.1 500 Internal Server Error");
	echo "User not found or archived.";
}
