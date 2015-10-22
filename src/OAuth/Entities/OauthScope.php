<?php
namespace Apitude\User\OAuth\Entities;

use Doctrine\ORM\Mapping as ORM;
use Apitude\Core\Annotations\API;

/**
 * @ORM\Entity()
 * @ORM\Table(name="oauth_scope")
 * @package Apitude\User\Entities
 */
class OauthScope
{
    /**
     * @var string
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="id", type="string", length=32, options={"CHARACTER SET"="ascii"})
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="description", type="string", options={"CHARACTER SET"="ascii"})
     */
    private $description;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return OauthScope
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return OauthScope
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }


}
