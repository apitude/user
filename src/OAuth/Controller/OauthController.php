<?php
namespace Apitude\User\OAuth\Controller;

use Apitude\Core\Application;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
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
}