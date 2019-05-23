<?php

namespace App\Listener;

use App\Entity\Storage\Storage;
use App\Entity\Storage\Version;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class StorageVersionListener
 *
 * @package App\Listener
 */
class StorageVersionListener
{
    /**
     * @param Version $version
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Version $version, LifecycleEventArgs $event)
    {
        if ($version->getIsActive() === true) {
            $this->expireVersions($event->getObjectManager(), $version->getStorage());
        }
    }

    /**
     * @param Version $version
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Version $version, PreUpdateEventArgs $event)
    {
        if ($version->getIsActive() === true) {
            $this->expireVersions($event->getObjectManager(), $version->getStorage());
        }
    }

    /**
     * @param Version $version
     * @param LifecycleEventArgs $event
     */
    public function preRemove(Version $version, LifecycleEventArgs $event)
    {
        if ($version->getIsActive() === true) {
            // Detach entity to prevent it removal
            $event->getObjectManager()->detach($version);

            // Send error state with response
            $response = new JsonResponse(['error' => 'Активная версия файла не может быть удалена.'], 400);
            $response->send();
        }
    }

    /**
     * @param ObjectManager $manager
     * @param Storage $storage
     */
    private function expireVersions(ObjectManager $manager, Storage $storage)
    {
        $manager->getRepository('App:Storage\Version')->expireVersions($storage);
    }
}