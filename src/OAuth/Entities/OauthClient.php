<?php
namespace Apitude\User\OAuth\Entities;

use Doctrine\ORM\Mapping as ORM;
use Apitude\Core\Annotations\API;

/**
 * @ORM\Entity()
 * @ORM\Table(name="oauth_client")
 * @API\Entity\Expose
 */
class OauthClient
{
    /**
     * @var string
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(name="id", type="string", length=64, options={"collation"="ascii_general_ci"})
     * @API\Property\Expose
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="secret", type="string", options={"collation"="ascii_general_ci"})
     */
    private $secret;

    /**
     * @var string
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return OauthClient
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     * @return OauthClient
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return OauthClient
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

}
