<?php
namespace Apitude\User\OAuth\Entities;

use Doctrine\ORM\Mapping as ORM;
use Apitude\Core\Annotations\API;

/**
 * Class OauthAccessToken
 * @ORM\Entity()
 * @ORM\Table(name="oauth_access_token")
 * @package Apitude\User\Entities
 */
class OauthAccessToken
{
    /**
     * @var string
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="access_token", type="string", length=64, options={"collation"="ascii_general_ci"})
     */
    private $accessToken;

    /**
     * @var int
     * @ORM\Column(name="session_id", type="integer", options={"unsigned"=true})
     */
    private $sessionId;

    /**
     * @var int
     * @ORM\Column(type="integer", name="expire_time", options={"unsigned"=true})
     */
    private $expireTime;

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     * @return OauthAccessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @return int
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param int $sessionId
     * @return OauthAccessToken
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    /**
     * @return int
     */
    public function getExpireTime()
    {
        return $this->expireTime;
    }

    /**
     * @param int $expireTime
     * @return OauthAccessToken
     */
    public function setExpireTime($expireTime)
    {
        $this->expireTime = $expireTime;
        return $this;
    }
}
