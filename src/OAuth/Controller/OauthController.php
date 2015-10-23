<?php
namespace Apitude\User\OAuth\Controller;

use Apitude\Core\Application;
use Apitude\User\Security\UserProvider;
use Apitude\user\Entities\User;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\AccessDeniedException;
use League\OAuth2\Server\Exception\OAuthException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\Util\RedirectUri;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OauthController
{
    /**
     * @param Application $app
     * @return AuthorizationServer
     */
    private function getAuthorizationServer(Application $app) {
        return $app[AuthorizationServer::class];
    }

    /**
     * @param Application $app
     * @return ResourceServer
     */
    private function getResourceServer(Application $app) {
        return $app[ResourceServer::class];
    }

    public function signinRedirect(Application $app) {
        try {
            session_start();
            /** @var AuthCodeGrant $grant */
            $grant = $this->getAuthorizationServer($app)->getGrantType('authorization_code');
            $authParams = $grant->checkAuthorizeParams();
            $authParams['client'] = $authParams['client']->getId();
            $authParams['scopes'] = array_keys($authParams['scopes']);
            $_SESSION['auth_params'] = $authParams;
            return $app->redirect('/oauth/signin');
        } catch(OAuthException $e) {
            if ($e->shouldRedirect()) {
                return new RedirectResponse($e->getRedirectUri());
            }
            return new JsonResponse(
                [
                    'error' => $e->errorType,
                    'message' => $e->getMessage()
                ],
                $e->httpStatusCode,
                $e->getHttpHeaders()
            );
        }
    }

    public function signinGet(Application $app, Request $request) {
        session_start();
        $authParams = $_SESSION['auth_params'];
        $authParams['client'] = $this->getAuthorizationServer($app)->getClientStorage()->get($authParams['client']);
        $scopeStorage = $this->getAuthorizationServer($app)->getScopeStorage();
        $authParams['scopes'] = array_map(function($item) use ($scopeStorage) {
            return $scopeStorage->get($item);
        }, $authParams['scopes']);
        ob_start();
        include(__DIR__.'/signin.phtml');
        return new Response(ob_get_clean(), 200, [
            'Content-Type' => 'text/html'
        ]);
    }

    public function signinPost(Application $app, Request $request) {
        session_start();
        $authParams = $_SESSION['auth_params'];
        $authParams['client'] = $this->getAuthorizationServer($app)->getClientStorage()->get($authParams['client']);
        $scopeStorage = $this->getAuthorizationServer($app)->getScopeStorage();
        $authParams['scopes'] = array_map(function($item) use ($scopeStorage) {
            return $scopeStorage->get($item);
        }, $authParams['scopes']);
        /** @var UserProvider $userProvider */
        $userProvider = $app[UserProvider::class];
        try {
            /** @var User $user */
            $user = $userProvider->loadUserByUsername($request->get('username'));
        } catch(\Exception $e) {
            return false;
        }

        if (password_verify($request->get('password'), $user->getPassword())) {
            if ($_POST['authorization'] === 'Approve') {
                /** @var AuthCodeGrant $grant */
                $grant = $this->getAuthorizationServer($app)
                    ->getGrantType('authorization_code');
                $redirect = $grant->newAuthorizeRequest('user', $user->getId(), $authParams);
                return $app->redirect($redirect);
            }
        }
        $error = new AccessDeniedException;
        $redirect = RedirectUri::make(
            $authParams['redirect_uri'],
            [
                'error' =>  $error->errorType,
                'message'   =>  $error->getMessage()
            ]
        );
        return $app->redirect($redirect);
    }

    public function accessToken(Application $app) {
        $server = $this->getAuthorizationServer($app);
        /** @var AuthCodeGrant $grant */
        $grant = $server->getGrantType('authorization_code');
        $grant->setRequireClientSecret(false);

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
        $server->isValidRequest();
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