<?php
namespace Apitude\User\Entities;

use Apitude\Core\Entities\AbstractEntity;
use Apitude\Core\EntityStubs\StampEntityInterface;
use Apitude\Core\EntityStubs\StampEntityTrait;
use Apitude\User\ORM\EntityStubs\UserStampEntityInterface;
use Apitude\User\ORM\EntityStubs\UserStampEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Apitude\Core\Annotations\API;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class User
 * @package Apitude\User\Entities
 * @ORM\Entity()
 * @ORM\Table(name="user_user")
 * @API\Entity\Expose()
 */
class User extends AbstractEntity implements StampEntityInterface, UserStampEntityInterface, UserInterface
{
    use StampEntityTrait;
    use UserStampEntityTrait;

    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @API\Property\Expose()
     */
    private $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @var string
     * @ORM\Column(name="username", type="string", unique=true, nullable=false)
     * @API\Property\Expose()
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", nullable=false)
     */
    private $password;

    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    private $email;

    /**
     * Note that this property is not unique, meaning the same email could be used for multiple users.
     * This would be useful for systems with multiple types of users or multi-site/domain systems, each with their
     * own sets of users.  For that strategy, the username should include the subsite to keep the username unique.
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="SecurityGroup", inversedBy="users")
     * @ORM\JoinTable(name="users_securitygroups")
     * @API\Property\Expose()
     */
    private $securityGroups;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $enabled = true;

    /**
     * @var array
     * @ORM\Column(type="array")
     * @API\Property\Expose()
     */
    private $roles = [];

    public function __construct()
    {
        $this->securityGroups = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getSecurityGroups()
    {
        return $this->securityGroups;
    }

    /**
     * @param ArrayCollection $securityGroups
     * @return User
     */
    public function setSecurityGroups($securityGroups)
    {
        $this->securityGroups = $securityGroups;

        return $this;
    }

    public function addRole($role) {
        $this->roles[] = $role;
        return $this;
    }

    public function setRoles($roles)
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return string[] The user roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // noop
    }
}
