<?php
namespace Apitude\User\OAuth\Entities;

use Doctrine\ORM\Mapping as ORM;
use Apitude\Core\Annotations\API;

/**
 * @ORM\Entity()
 * @ORM\Table(name="oauth_session")
 * @package Apitude\User\Entities
 */
class OauthSession
{
    /**
     * @var string
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="owner_type", type="string", options={"collation"="ascii_general_ci"})
     */
    private $ownerType;

    /**
     * @var string
     * @ORM\Column(name="owner_id", type="string", options={"collation"="ascii_general_ci"})
     */
    private $ownerId;

    /**
     * @var string
     * @ORM\Column(name="client_id", type="string", length=64, options={"collation"="ascii_general_ci"})
     */
    private $clientId;

    /**
     * @var OauthClientRedirectUri
     * @ORM\OneToOne(targetEntity="OauthClientRedirectUri")
     * @ORM\JoinColumn(name="client_redirect_uri", referencedColumnName="id")
     */
    private $clientRedirectUri;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return OauthSession
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getOwnerType()
    {
        return $this->ownerType;
    }

    /**
     * @param string $ownerType
     * @return OauthSession
     */
    public function setOwnerType($ownerType)
    {
        $this->ownerType = $ownerType;
        return $this;
    }

    /**
     * @return string
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @param string $ownerId
     * @return OauthSession
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;
        return $this;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     * @return OauthSession
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @return OauthClientRedirectUri
     */
    public function getClientRedirectUri()
    {
        return $this->clientRedirectUri;
    }

    /**
     * @param OauthClientRedirectUri $clientRedirectUri
     * @return OauthSession
     */
    public function setClientRedirectUri($clientRedirectUri)
    {
        $this->clientRedirectUri = $clientRedirectUri;
        return $this;
    }

}
