<?php 

// include our OAuth2 Server object
require_once '../bootstrap.php';
require_once '../src/DBAdditional.php';

$request = OAuth2\Request::createFromGlobals();
// Handle a request to a resource and authenticate the access token
if (!$server->verifyResourceRequest($request) ) {
    $server->getResponse()->send();
    die;
}

$db_additional = new DBAdditional(array('dsn' => getenv('DB_DSN'), 'username' => getenv('DB_USERNAME'), 'password' => getenv('DB_PASSWORD') )); 
if($db_additional->setSessionsInactive($request->query("session_id"),'logout') ) {
	//do the logout work
	echo json_encode(array('success' => true, 'message' => 'You successfully logged out'));
}
else {
	echo json_encode(array('success' => false, 'message' => 'there was some error while deleting the session'));	
}
