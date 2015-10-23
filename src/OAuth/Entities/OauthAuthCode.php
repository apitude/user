<?php
namespace Apitude\User\OAuth\Entities;

use Apitude\User\OAuth\Entities\OauthSession;
use Doctrine\ORM\Mapping as ORM;
use Apitude\Core\Annotations\API;

/**
 * @ORM\Entity()
 * @ORM\Table(name="oauth_auth_code")
 * @package Apitude\User\Entities
 */
class OauthAuthCode
{
    /**
     * @var string
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="auth_code", type="string", length=128, options={"collation"="ascii_general_ci"})
     */
    private $authCode;

    /**
     * @var OauthSession
     * @ORM\OneToOne(targetEntity="OauthSession")
     * @ORM\JoinColumn(name="session_id", referencedColumnName="id")
     */
    private $session;

    /**
     * @var int
     * @ORM\Column(name="expire_time", type="integer", options={"unsigned"=true})
     */
    private $expireTime;

    /**
     * @var string
     * @ORM\Column(type="string", name="client_redirect_uri")
     */
    private $clientRedirectUri;

    /**
     * @return string
     */
    public function getAuthCode()
    {
        return $this->authCode;
    }

    /**
     * @param string $authCode
     * @return OauthAuthCode
     */
    public function setAuthCode($authCode)
    {
        $this->authCode = $authCode;
        return $this;
    }

    /**
     * @return OauthSession
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param OauthSession $session
     * @return OauthAuthCode
     */
    public function setSession($session)
    {
        $this->session = $session;
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
     * @return OauthAuthCode
     */
    public function setExpireTime($expireTime)
    {
        $this->expireTime = $expireTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getClientRedirectUri()
    {
        return $this->clientRedirectUri;
    }

    /**
     * @param string $clientRedirectUri
     * @return OauthAuthCode
     */
    public function setClientRedirectUri($clientRedirectUri)
    {
        $this->clientRedirectUri = $clientRedirectUri;
        return $this;
    }

}
