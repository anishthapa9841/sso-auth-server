<?php

use OAuth2\OpenID\Controller\AuthorizeController as BaseAuthorizeController;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;

/**
 * @see OAuth2\Controller\AuthorizeControllerInterface
 */
class AuthCustController extends BaseAuthorizeController
{
    
    protected function buildAuthorizeParameters($request, $response, $user_id)
    {
        if (!$params = parent::buildAuthorizeParameters($request, $response, $user_id)) {
            return;
        }

        // Generate an id token if needed.
        if ($this->needsIdToken($this->getScope()) && $this->getResponseType() == self::RESPONSE_TYPE_AUTHORIZATION_CODE) {
            $params['id_token'] = $this->responseTypes['id_token']->createIdToken($this->getClientId(), $user_id, $this->nonce);
        }

        // add the nonce to return with the redirect URI
        $params['nonce'] = $this->nonce;

        return $params;
    }

}
