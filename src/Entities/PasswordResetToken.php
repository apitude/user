<?php
namespace Apitude\User\Entities;

use Apitude\Core\Entities\AbstractEntity;
use Apitude\Core\EntityStubs\StampEntityInterface;
use Apitude\Core\EntityStubs\StampEntityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class PasswordResetToken
 * @package Apitude\User\Entities
 * @ORM\Entity
 * @ORM\Table(name="user_password_reset_token")
 */
class PasswordResetToken extends AbstractEntity implements StampEntityInterface
{
    use StampEntityTrait;

    /**
     * @var string
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="string", length=64, columnDefinition="VARCHAR(64) COLLATE ascii_general_ci")
     */
    private $token;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $expires;

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return PasswordResetToken
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return PasswordResetToken
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param \DateTime $expires
     * @return PasswordResetToken
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
        return $this;
    }
}
