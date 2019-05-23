<?php

namespace App\Listener;

use App\Entity\Role;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class RoleListener
 *
 * @package App\Listener
 */
class RoleListener
{
    /**
     * @param Role $role
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Role $role, LifecycleEventArgs $event)
    {
        if ($role->getIsDefault() === true) {
            $this->disableDefaultStatus($event->getObjectManager());
        }
    }

    /**
     * @param Role $role
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Role $role, PreUpdateEventArgs $event)
    {
        if ($role->getIsDefault() === true) {
            $this->disableDefaultStatus($event->getObjectManager());
        }
    }

    /**
     * @param Role $role
     * @param LifecycleEventArgs $event
     */
    public function preRemove(Role $role, LifecycleEventArgs $event)
    {
        if ($role->getIsDefault() === true) {
            // Detach entity to prevent it removal
            $event->getObjectManager()->detach($role);

            // Send error state with response
            $response = new JsonResponse(['error' => 'Роль в статусе "По-умолчанию" не может быть удалена.'], 400);
            $response->send();
        }
    }

    /**
     * @param ObjectManager $manager
     */
    private function disableDefaultStatus(ObjectManager $manager)
    {
        $manager->getRepository('App:Role')->disableDefaultStatus();
    }
}