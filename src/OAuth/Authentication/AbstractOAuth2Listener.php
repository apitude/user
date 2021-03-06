<?php
namespace Apitude\User\OAuth\Authentication;

use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;
use Apitude\Core\Provider\Helper\EntityManagerAwareInterface;
use Apitude\Core\Provider\Helper\EntityManagerAwareTrait;
use Apitude\User\Entities\User;
use League\OAuth2\Server\Exception\OAuthException;
use League\OAuth2\Server\ResourceServer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

abstract class AbstractOAuth2Listener implements EntityManagerAwareInterface, ContainerAwareInterface, ListenerInterface
{
    use ContainerAwareTrait;
    use EntityManagerAwareTrait;

    protected function doHandle(GetResponseEvent $event)
    {
        /** @var ResourceServer $server */
        $server = $this->container[ResourceServer::class];
        try {
            $server->isValidRequest();
            $token = $server->getAccessToken();
            /** @var User $user */
            $user = $this->getEntityManager()->find(User::class, $token->getSession()->getOwnerId());
            if (!$user->isEnabled()) {
                $event->setResponse($this->getInvalidTokenReponse());
                return;
            }
            $authToken = new OAuthToken(
                $user,
                $token->getScopes(),
                $token->getSession()->getClient()->getId(),
                $user->getRoles()
            );
            $this->getTokenStorage()->setToken($authToken);
            return;
        } catch(OAuthException $e) {
            $event->setResponse($this->getInvalidTokenReponse());
        }
    }

    /**
     * @return TokenStorageInterface
     */
    protected function getTokenStorage() {
        return $this->container['security.token_storage'];
    }

    /**
     * Return an invalid_token response object
     *
     * @return JsonResponse
     */
    protected function getInvalidTokenReponse()
    {
        return $this->getUnauthorizedResponse(Response::HTTP_UNAUTHORIZED, 'invalid_token');
    }

    /**
     * Return an invalid_request response object
     *
     * @return JsonResponse
     */
    protected function getInvalidRequestResponse()
    {
        return $this->getUnauthorizedResponse(Response::HTTP_UNAUTHORIZED, 'invalid_request');
    }

    /**
     * Return an "Unauthorized" request including a WWW-Authenticate header per
     * the OAuth2 specification
     *
     * @param  integer $statusCode HTTP status code to return in response
     * @param  string  $error      Error message in WWW-Authenticate header
     * @return JsonResponse
     * @link   https://tools.ietf.org/html/rfc6750#section-3
     */
    protected function getUnauthorizedResponse($statusCode, $error = null)
    {
        $authenticateHeader  = 'Bearer';
        $authenticateHeader .= $error === null ? '' : sprintf(' error="%s"', $error);

        $body    = ['message' => 'Unauthorized'];
        $headers = ['WWW-Authenticate' => $authenticateHeader];

        return new JsonResponse($body, $statusCode, $headers);
    }
}
