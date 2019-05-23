<?php

namespace App\Listener;

use App\Entity\Account\Type;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class AccountTypeListener
 *
 * @package App\Listener
 */
class AccountTypeListener
{
    /**
     * @param Type $accountType
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Type $accountType, LifecycleEventArgs $event)
    {
        if ($accountType->getIsDefault() === true) {
            $this->disableDefaultStatus($event->getObjectManager());
        }
    }

    /**
     * @param Type $accountType
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Type $accountType, PreUpdateEventArgs $event)
    {
        if ($accountType->getIsDefault() === true) {
            $this->disableDefaultStatus($event->getObjectManager());
        }
    }

    /**
     * @param Type $accountType
     * @param LifecycleEventArgs $event
     */
    public function preRemove(Type $accountType, LifecycleEventArgs $event)
    {
        if ($accountType->getIsDefault() === true) {
            // Detach entity to prevent it removal
            $event->getObjectManager()->detach($accountType);

            // Send error state with response
            $response = new JsonResponse(['error' => 'Тип контрагента с параметром "По-умолчанию" не может быть удален.'], 400);
            $response->send();
        }
    }

    /**
     * @param ObjectManager $manager
     */
    private function disableDefaultStatus(ObjectManager $manager)
    {
        $manager->getRepository('App:Account\Type')->disableDefaultStatus();
    }
}