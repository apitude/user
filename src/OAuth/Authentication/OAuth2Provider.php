<?php
namespace Apitude\User\OAuth\Authentication;

use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;
use Apitude\Core\Provider\Helper\EntityManagerAwareInterface;
use Apitude\Core\Provider\Helper\EntityManagerAwareTrait;
use Apitude\User\Entities\User;
use League\OAuth2\Server\Exception\OAuthException;
use League\OAuth2\Server\ResourceServer;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class OAuth2Provider implements AuthenticationProviderInterface, ContainerAwareInterface, EntityManagerAwareInterface
{
    use ContainerAwareTrait;
    use EntityManagerAwareTrait;

    /**
     * Attempts to authenticate a TokenInterface object.
     *
     * @param TokenInterface $token The TokenInterface instance to authenticate
     *
     * @return TokenInterface An authenticated TokenInterface instance, never null
     *
     * @throws AuthenticationException if the authentication fails
     */
    public function authenticate(TokenInterface $token)
    {
        /** @var ResourceServer $server */
        $server = $this->container[ResourceServer::class];
        try {
            $server->isValidRequest();
            $token = $server->getAccessToken();
            $user = $this->getEntityManager()->find(User::class, $token->getSession()->getOwnerId());
            $authToken = new OAuthToken(
                $user,
                $token->getScopes(),
                $token->getSession()->getClient()->getId(),
                $user->getRoles()
            );
            return $authToken;
        } catch(OAuthException $e) {
            throw new AuthenticationException($e->getMessage());
        }
    }

    /**
     * Checks whether this provider supports the given token.
     *
     * @param TokenInterface $token A TokenInterface instance
     *
     * @return bool true if the implementation supports the Token, false otherwise
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof OAuthToken;
    }
}
