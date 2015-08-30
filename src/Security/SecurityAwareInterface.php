<?php
namespace Apitude\User\Security;

use Apitude\User\Entities\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface SecurityAwareInterface
{
    /**
     * @return TokenStorageInterface
     */
    public function getTokenStorage();

    /**
     * @return UserInterface|User
     */
    public function getCurrentUser();
}
