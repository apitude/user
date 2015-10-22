<?php
namespace Apitude\User\ORM\EntityStubs;

use Apitude\User\Entities\User;

trait UserStampEntityTrait
{
    /**
     * @var User
     * @ORM\OneToOne(targetEntity="Apitude\User\Entities\User")
     * @ORM\JoinColumn(name="create_user_id", referencedColumnName="id")
     * @API\Property\Expose()
     */
    private $createdBy;

    /**
     * @var User
     * @ORM\OneToOne(targetEntity="Apitude\User\Entities\User")
     * @ORM\JoinColumn(name="modify_user_id", referencedColumnName="id")
     * @API\Property\Expose()
     */
    private $modifiedBy;

    /**
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param User $createdBy
     * @return self
     */
    public function setCreatedBy(User $createdBy = null)
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * @return User
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * @param User $modifiedBy
     * @return self
     */
    public function setModifiedBy(User $modifiedBy = null)
    {
        $this->modifiedBy = $modifiedBy;
        return $this;
    }
}
