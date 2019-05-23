<?php

namespace App\Listener;

use App\Entity\AddressType;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * Class AddressTypeListener
 *
 * @package App\Listener
 */
class AddressTypeListener
{
    /**
     * @param AddressType $addressType
     * @param LifecycleEventArgs $event
     */
    public function prePersist(AddressType $addressType, LifecycleEventArgs $event)
    {
        if ($addressType->getIsDefault() === true) {
            $this->disableDefaultStatus($event->getObjectManager());
        }
    }

    /**
     * @param AddressType $addressType
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(AddressType $addressType, PreUpdateEventArgs $event)
    {
        if ($addressType->getIsDefault() === true) {
            $this->disableDefaultStatus($event->getObjectManager(), $addressType);
        }
    }

    /**
     * @param ObjectManager $manager
     * @param AddressType|null $addressType
     */
    private function disableDefaultStatus(ObjectManager $manager, AddressType $addressType = null)
    {
        $manager->getRepository('App:AddressType')->disableDefaultStatus($addressType);
    }
}