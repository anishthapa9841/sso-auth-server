<?php

use OAuth2\Encryption\EncryptionInterface;
use OAuth2\Encryption\Jwt;
use OAuth2\Storage\PublicKeyInterface;
use OAuth2\OpenID\Storage\UserClaimsInterface;

class IdTokenCust extends OAuth2\OpenID\ResponseType\IdToken
{

    /**
     * Create id token
     *
     * @param string $client_id
     * @param mixed  $userInfo
     * @param mixed  $nonce
     * @param mixed  $userClaims
     * @param mixed  $access_token
     * @return mixed|string
     */
    public function createIdToken($client_id, $userInfo, $nonce = null, $userClaims = null, $access_token = null)
    {
        // pull auth_time from user info if supplied
        list($user_id, $auth_time) = $this->getUserIdAndAuthTime($userInfo);
        $user_data = $this->userClaimsStorage->getUser($user_id) ;
        $client_data = $this->userClaimsStorage->getClientDetails($client_id) ;
        $token = array(
            'iss'        => $this->config['issuer'],
            'sub'        => $user_id,
            'name'       => $user_data['user_pk_id'],
            'email'      => $user_data['email'],
            'aud'        => $client_id,
            'iat'        => time(),
            'exp'        => time() + $this->config['id_lifetime'],
            'auth_time'  => $auth_time,
            'scope'      => $client_data["scope"],
            'amr'        => array("pwd"),
        );

        if ($nonce) {
            $nonce_data = explode("|",$nonce);
            $token['nonce'] = $nonce_data[0];
            $token['state'] = $nonce_data[1];
            $token['jti'] = $this->createAtHash($nonce, time());
        }

        if ($userClaims) {
            $token += $userClaims;
        }

        if ($access_token) {
            $token['at_hash'] = $this->createAtHash($access_token, $client_id);
        }

        return $this->encodeToken($token, $client_id);
    }

    /**
     * @param $userInfo
     * @return array
     * @throws LogicException
     */
    private function getUserIdAndAuthTime($userInfo)
    {
        $auth_time = null;

        // support an array for user_id / auth_time
        if (is_array($userInfo)) {
            if (!isset($userInfo['user_id'])) {
                throw new LogicException('if $user_id argument is an array, user_id index must be set');
            }

            $auth_time = isset($userInfo['auth_time']) ? $userInfo['auth_time'] : null;
            $user_id = $userInfo['user_id'];
        } else {
            $user_id = $userInfo;
        }

        if (is_null($auth_time)) {
            $auth_time = time();
        }

        // userInfo is a scalar, and so this is the $user_id. Auth Time is null
        return array($user_id, $auth_time);
    }
}
