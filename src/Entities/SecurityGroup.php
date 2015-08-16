<?php
namespace Apitude\User\Entities;

use Apitude\Core\Entities\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Apitude\Core\Annotations\API;

/**
 * Class SecurityGroup
 * @package Apitude\User\Entities
 * @ORM\Entity()
 * @ORM\Table(name="user_security_group")
 */
class SecurityGroup extends AbstractEntity
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @API\Property\Expose()
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", unique=true)
     * @API\Property\Expose()
     */
    private $name;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="User", mappedBy="groups")
     * @API\Property\Expose()
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return SecurityGroup
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }
}
