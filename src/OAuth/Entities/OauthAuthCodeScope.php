<?php
namespace Apitude\User\OAuth\Entities;

use Apitude\User\OAuth\Entities\OauthScope;
use Apitude\User\OAuth\Entities\OauthAuthCode;
use Doctrine\ORM\Mapping as ORM;
use Apitude\Core\Annotations\API;

/**
 * @ORM\Entity()
 * @ORM\Table(name="oauth_auth_code_scope")
 * @package Apitude\User\Entities
 */
class OauthAuthCodeScope
{
    /**
     * @var string
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer", length=32, options={"unsigned"=true})
     */
    private $id;

    /**
     * @var OauthAuthCode
     * @ORM\ManyToOne(targetEntity="OauthAuthCode")
     * @ORM\JoinColumn(name="auth_code", referencedColumnName="id")
     */
    private $authCode;

    /**
     * @var OauthScope
     * @ORM\OneToOne(targetEntity="OauthScope")
     * @ORM\JoinColumn(name="oauth_scope", referencedColumnName="id")
     */
    private $scope;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return OauthAuthCodeScope
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return OauthAuthCode
     */
    public function getAuthCode()
    {
        return $this->authCode;
    }

    /**
     * @param OauthAuthCode $authCode
     * @return OauthAuthCodeScope
     */
    public function setAuthCode($authCode)
    {
        $this->authCode = $authCode;
        return $this;
    }

    /**
     * @return OauthScope
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param OauthScope $scope
     * @return OauthAuthCodeScope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }


}
