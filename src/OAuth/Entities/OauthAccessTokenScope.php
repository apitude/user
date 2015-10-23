<?php
namespace Apitude\User\OAuth\Entities;

use Doctrine\ORM\Mapping as ORM;
use Apitude\Core\Annotations\API;

/**
 * Class OauthAccessToken
 * @ORM\Entity()
 * @ORM\Table(name="oauth_access_token_scope")
 * @package Apitude\User\Entities
 */
class OauthAccessTokenScope
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
     */
    private $id;

    /**
     * @var OauthAccessToken
     * @ORM\ManyToOne(targetEntity="OauthAccessToken")
     * @ORM\JoinColumn(name="access_token", referencedColumnName="access_token", columnDefinition="VARCHAR(64) DEFAULT NULL COLLATE ascii_general_ci")
     */
    private $accessToken;

    /**
     * @var OauthScope
     * @ORM\ManyToOne(targetEntity="OauthScope")
     * @ORM\JoinColumn(name="scope", referencedColumnName="id", columnDefinition="VARCHAR(32) DEFAULT NULL COLLATE ascii_general_ci")
     */
    private $scope;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return OauthAccessTokenScope
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return OauthAccessTokenScope
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
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
     * @return OauthAccessTokenScope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }
}
