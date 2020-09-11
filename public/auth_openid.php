<?php 

require_once '../bootstrap.php';
require_once '../src/DBAdditional.php';

// if the login is in process then read post data and check its credential with the database 
if(isset($_POST["submit"]) ) {
	if($storage->checkUserCredentials($_POST["username"], $_POST["password"]) ) {
		$request_data = json_decode($_POST["request_data"], true);
		$request = new OAuth2\Request(array(
		    'client_id'     => $request_data["client_id"],
		    'redirect_uri'  => $request_data["redirect_uri"],
		    'response_type' => $request_data["response_type"],
		    'scope'         => $request_data["scope"],
		    'state'         => $request_data["state"],
		    'nonce'					=> $request_data["session"]."|".$request_data["state"],
		));
		$response = new OAuth2\Response();
		$server->handleAuthorizeRequest($request, $response, true, $_POST["username"] );
		$parts = parse_url($response->getHttpHeader('Location'));
		parse_str($parts['query'], $query);

		// pull the code from storage and verify an "id_token" was added
		$code = $server->getStorage('authorization_code')
		        ->getAuthorizationCode($query['code']);

		//add the session to the database
		$db_additional = new DBAdditional(array('dsn' => getenv('DB_DSN'), 'username' => getenv('DB_USERNAME'), 'password' => getenv('DB_PASSWORD') )); 
		if(!$db_additional->createNewSession($request_data["session"], $_POST["username"], $request_data["client_id"], $request_data["state"]) ) {
			$error_msg = "There was some error while logging in";
		}
		else {
			//header to the id token url
			header("Location: {$code["redirect_uri"]}?code={$code["authorization_code"]}&id_token={$code["id_token"]}");
			exit;
		}

	}
	else {
		$error_msg = "Invalid username or password";
	}
}

//check for the valid request using scope and response type is code or not
if(isset($_GET["scope"]) && isset($_GET["response_type"]) && $_GET["scope"] == "openid" 
	&& $_GET["response_type"] == "code" && isset($_GET["client_id"]) 
	&& isset($_GET["state"]) && isset($_GET["session"]) )  {
	//check if that session is already in the database or not if not then 
	$db_additional = new DBAdditional(array('dsn' => getenv('DB_DSN'), 'username' => getenv('DB_USERNAME'), 'password' => getenv('DB_PASSWORD') )); 
	if($db_additional->checkSessionId($_GET["session"])) {
		$db_additional->setSessionsInactive($_GET["session"], 'forced');
	}
	//call the view
	require_once("views/auth_openid_view.php");
}
else {
	echo json_encode(array("state"=>"400 Bad Request", "message"=>"invalid request"));
}
