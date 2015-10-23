<?php
namespace Apitude\User\OAuth\Controller;

use Apitude\Core\Application;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
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

    public function authorizeGet() {
        $inputs = [];
        foreach ($_GET as $key=>$value) {
            $inputs[] = <<<HTML
<input type="hidden" name="{$key}" value="{$value}"/>
HTML;
        }

        $inputs = implode('', $inputs);
        return new Response(
            <<<HTML
<html>
    <body>
        <form method="post">
            {$inputs}
            <label>Username: <input type="text" name="username"/></label>
            <label>Password: <input type="password" name="password"/></label>
            <input type="submit" value="Login"/>
        </form>
    </body>
</html>
HTML
            ,
            200,
            ['Content-Type' => 'text/html']
        );
    }

    public function authorizePost(Application $app, Request $request) {
        $server = $this->getAuthorizationServer($app);
        try {
            /** @var PasswordGrant $grant */
            $grant = $server->getGrantType('web_password');
            $response =  $grant->completeFlow();
            if ($response) {
                return new JsonResponse($response);
            }
        } catch(\Exception $e) {
            return new JsonResponse([
                'error' => $e->getCode(),
                'message' => $e->getMessage()
            ]);
        }

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