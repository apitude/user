<?php


namespace Apitude\User\OAuth\Authentication;


use Apitude\User\Entities\User;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Role\RoleInterface;

class OAuthToken extends AbstractToken
{
    private $client_id;
    private $scopes;

    /**
     * Constructor.
     *
     * @param string|object            $user        The username (like a nickname, email address, etc.), or a UserInterface instance or an object implementing a __toString method.
     * @param array $scopes
     * @param string $client_id
     * @param RoleInterface[]|string[] $roles       An array of roles
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($user, array $scopes, $client_id, array $roles = array())
    {
        parent::__construct($roles);

        $this->setUser($user);
        $this->client_id = $client_id;
        $this->scopes = $scopes;

        parent::setAuthenticated(count($roles) > 0);
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthenticated($isAuthenticated)
    {
        if ($isAuthenticated) {
            throw new \LogicException('Cannot set this token to trusted after instantiation.');
        }

        parent::setAuthenticated(false);
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        parent::eraseCredentials();
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return serialize(array($this->client_id, $this->getUser()->getUsername(), parent::serialize()));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->client_id, $username, $parentStr) = unserialize($serialized);
        parent::unserialize($parentStr);
        $this->setUser((new User())->setUsername($username));
    }

    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials()
    {
        return null;
    }
}