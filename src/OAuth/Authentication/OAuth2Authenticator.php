<?php
namespace Apitude\User\OAuth\Authentication;

use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;
use Apitude\User\Entities\User;
use Apitude\User\Security\UserProvider;

class OAuth2Authenticator implements OAuth2AuthenticatorInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Find a specific user based on a set of parameters
     * @param array $params
     * @return User|boolean
     */
    public function findUser(array $params)
    {
        /** @var UserProvider $userProvider */
        $userProvider = $this->container[UserProvider::class];
        try {
            /** @var User $user */
            $user = $userProvider->loadUserByUsername($params['username']);
        } catch(\Exception $e) {
            return false;
        }
        return $user;
    }

    /**
     * Returns user id or false if unable to authenticate
     * @param User $user
     * @param array $params
     * @return mixed
     */
    public function authenticate(User $user, array $params = [])
    {
        if ($user->isEnabled() && password_verify($params['password'], $user->getPassword())) {
            return $user->getId();
        }
        return false;
    }
}
