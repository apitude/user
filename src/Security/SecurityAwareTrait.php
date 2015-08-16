<?php
namespace Apitude\User\Security;

use Apitude\Core\Application;
use Apitude\User\Entities\User;

/**
 * Class SecurityAwareTrait
 * @package Apitude\User\Security
 * @property Application container
 */
trait SecurityAwareTrait
{
    function getTokenStorage()
    {
        return $this->container['security.token_storage'];
    }

    /**
     * @return User
     */
    function getCurrentUser()
    {
        return $this->container['user'];
    }
}
