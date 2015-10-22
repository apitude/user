<?php
namespace Apitude\User\OAuth;


use Apitude\Core\Application;
use League\OAuth2\Server\Exception\OAuthException;
use League\OAuth2\Server\ResourceServer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait OauthProviderTrait
{
    /**
     * Middleware to be used in before()
     * @param Application $app
     * @return JsonResponse
     */
    protected function requireAuthorization(Application $app)
    {
        /** @var ResourceServer $server */
        $server = $app[ResourceServer::class];
        try {
            $server->isValidRequest(false);
        } catch(OAuthException $e) {
            return new JsonResponse(
                [
                    'error' => $e->errorType,
                    'message' => $e->getMessage(),
                ],
                $e->httpStatusCode,
                $e->getHttpHeaders()
            );
        }
        return null;
    }

    /**
     * Middleware to be used in before()
     * @param Application $app
     * @param string $scopeName
     * @return JsonResponse
     */
    protected function requireScope(Application $app, $scopeName)
    {
        /** @var ResourceServer $server */
        $server = $app[ResourceServer::class];
        if ($response = $this->requireAuthorization($app)) {
            return $response;
        }
        $accessToken = $server->getAccessToken();
        if (!$accessToken->hasScope($scopeName)) {
            return new JsonResponse(
                [
                    'error' => 'UNAUTHORIZED',
                    'message' => 'Token does not contain scope: '.$scopeName
                ],
                Response::HTTP_FORBIDDEN
            );
        }
    }
}
