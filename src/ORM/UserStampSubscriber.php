<?php
namespace Apitude\User\ORM;

use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;
use Apitude\User\ORM\EntityStubs\UserStampEntityInterface;
use Apitude\User\Security\SecurityAwareInterface;
use Apitude\User\Security\SecurityAwareTrait;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class UserStampSubscriber implements EventSubscriber, SecurityAwareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;
    use SecurityAwareTrait;
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof UserStampEntityInterface) {
            $user = $this->getCurrentUser();
            if ($user) {
                $entity->setCreatedBy($user);
                $entity->setModifiedBy($user);
            }
        }
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof UserStampEntityInterface) {
            $user = $this->getCurrentUser();
            if ($user) {
                $entity->setModifiedBy($user);
            }
        }
    }
}
