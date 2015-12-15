<?php
namespace Apitude\User\Security;

use Apitude\Core\Application;
use Apitude\User\Entities\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class SecurityAwareTrait
 * @package Apitude\User\Security
 * @property Application container
 */
trait SecurityAwareTrait
{
    /**
     * @return TokenStorageInterface
     */
    function getTokenStorage()
    {
        return $this->container['security.token_storage'];
    }

    /**
     * @return User
     */
    function getCurrentUser()
    {
        $storage = $this->getTokenStorage();
        $token = $storage->getToken();
        return $token->getUser();
    }
}
