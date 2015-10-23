<?php
namespace Apitude\User\OAuth\Entities;

use Doctrine\ORM\Mapping as ORM;
use Apitude\Core\Annotations\API;

/**
 * @ORM\Entity()
 * @ORM\Table(name="oauth_refresh_token")
 * @package Apitude\User\Entities
 */
class OauthRefreshToken
{
    /**
     * @var string
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="refresh_token", type="string", length=64)
     */
    private $refreshToken;

    /**
     * @var int
     * @ORM\Column(type="integer", name="expire_time", options={"unsigned"=true})
     */
    private $expireTime;

    /**
     * @var OauthAccessToken
     * @ORM\OneToOne(targetEntity="OauthAccessToken")
     * @ORM\JoinColumn(name="access_token", referencedColumnName="access_token", columnDefinition="VARCHAR(64) DEFAULT NULL COLLATE ascii_general_ci")
     */
    private $accessToken;

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     * @return OauthAccessToken
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
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

    /**
     * @return OauthAccessToken
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param OauthAccessToken $accessToken
     * @return OauthRefreshToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }
}
