<?php
namespace Apitude\User\OAuth\Authentication;

use Apitude\User\Entities\User;

interface OAuth2AuthenticatorInterface
{
    /**
     * Find a specific user based on a set of parameters
     * @param array $params
     * @return User|boolean
     */
    public function findUser(array $params);

    /**
     * @param User $user
     * @param array $params
     * @return bool
     */
    public function authenticate(User $user, array $params = []);
}