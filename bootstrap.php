<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Kathmandu');
require_once("vendor/autoload.php");
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

OAuth2\Autoloader::register();
$storage = new OAuth2\Storage\Pdo(array('dsn' => getenv('DB_DSN'), 'username' => getenv('DB_USERNAME'), 'password' => getenv('DB_PASSWORD') ));

$server = new OAuth2\Server($storage, array(
	"use_jwt_access_tokens" => true,
	"use_openid_connect" => true, 
	"issuer" => getenv('OPENID_ISSUER')
));

$scope = new OAuth2\Scope($storage);
$server->setScopeUtil($scope);

$server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));
$server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));

//add custom response type
require_once("src/IdTokenCust.php");
$user_claims = $server->getStorage('user_claims');
if (!isset($user_claims) ) {
    throw new LogicException("You must supply a storage object implementing OAuth2\OpenID\Storage\UserClaimsInterface to use openid connect");
}
$public_key = $server->getStorage('public_key');
if (!isset($public_key) ) {
    throw new LogicException("You must supply a storage object implementing OAuth2\Storage\PublicKeyInterface to use openid connect");
}
$cust_config = array("id_lifetime" => $server->getConfig("id_lifetime"), "issuer" => $server->getConfig("issuer") );


$idToken = new IdTokenCust($user_claims, $public_key, $cust_config);
$server->addResponseType($idToken);

$openid_auth_code = new OAuth2\OpenID\ResponseType\AuthorizationCode($server->getStorage('authorization_code'));
$server->addResponseType($openid_auth_code);

$code_id_token = new OAuth2\OpenID\ResponseType\CodeIdToken($server->getResponseType("code"),$server->getResponseType("id_token"));
$server->addResponseType($openid_auth_code);