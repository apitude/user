<?php
namespace Apitude\User\OAuth\Entities;

use Doctrine\ORM\Mapping as ORM;
use Apitude\Core\Annotations\API;

/**
 * @ORM\Entity()
 * @ORM\Table(name="oauth_session_scope")
 * @package Apitude\User\Entities
 */
class OauthSessionScope
{
    /**
     * @var string
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer", length=32, options={"unsigned"=true})
     */
    private $id;

    /**
     * @var OauthSession
     * @ORM\ManyToOne(targetEntity="OauthSession")
     * @ORM\JoinColumn(name="session_id", referencedColumnName="id")
     */
    private $session;

    /**
     * @var OauthScope
     * @ORM\ManyToOne(targetEntity="OauthScope")
     * @ORM\JoinColumn(name="scope", referencedColumnName="id", columnDefinition="VARCHAR(32) DEFAULT NULL COLLATE ascii_general_ci")
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
     * @return OauthSessionScope
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return OauthSessionScope
     */
    public function setSession($session)
    {
        $this->session = $session;
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
     * @return OauthSessionScope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }

}
