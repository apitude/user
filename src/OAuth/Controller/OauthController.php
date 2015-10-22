<?php
namespace Apitude\User\OAuth\Controller;

use Apitude\Core\Application;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OauthController
{
    /**
     * @param Application $app
     * @return AuthorizationServer
     */
    private function getAuthorizationServer(Application $app) {
        static $authServer;
        if (!$authServer) {
            /** @var AuthorizationServer $server */
            $authServer = $app[AuthorizationServer::class];
            $authServer->addGrantType(new AuthCodeGrant())
                ->addGrantType(new RefreshTokenGrant());
        }
        return $authServer;
    }

    /**
     * @param Application $app
     * @return ResourceServer
     */
    private function getResourceServer(Application $app) {
        return $app[ResourceServer::class];
    }

    public function authorize(Application $app, Request $request) {
        $server = $this->getAuthorizationServer($app);
        try {
            $authParams = $server->getGrantType('authorization_code')->checkAuthorizeParams();
        } catch(\Exception $e) {
            return new JsonResponse([
                'error' => $e->getCode(),
                'message' => $e->getMessage()
            ]);
        }

        // show form?

        $redirectUri = $server->getGrantType('authorization_code')->newAuthorizeRequest('user', 1, $authParams);
        $response = new Response('', 200, [
            'Location'  =>  $redirectUri
        ]);
        return $response;
    }

    public function accessToken(Application $app, Request $request) {
        $server = $this->getAuthorizationServer($app);

        try {
            $response = $server->issueAccessToken();
            return new JsonResponse($response);
        } catch (OAuthException $e) {
            return new JsonResponse(
                [
                    'error'     =>  $e->errorType,
                    'message'   =>  $e->getMessage(),
                ],
                $e->httpStatusCode ?: 500,
                $e->getHttpHeaders()
            );
        }
    }

    public function tokenInfo(Application $app) {
        $server = $this->getResourceServer($app);
        $accessToken = $server->getAccessToken();
        $session = $server->getSessionStorage()->getByAccessToken($accessToken);
        $token = [
            'owner_id' => $session->getOwnerId(),
            'owner_type' => $session->getOwnerType(),
            'access_token' => $accessToken,
            'client_id' => $session->getClient()->getId(),
            'scopes' => $accessToken->getScopes(),
        ];
        return new JsonResponse($token);
    }
}