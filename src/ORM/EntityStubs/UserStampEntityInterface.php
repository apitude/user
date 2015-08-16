<?php
namespace Apitude\User\ORM\EntityStubs;


use Apitude\User\Entities\User;

interface UserStampEntityInterface
{
    public function setCreatedBy(User $user = null);
    public function setModifiedBy(User $user = null);
    public function getCreatedBy();
    public function getModifiedBy();
}
