<?php

namespace App\Listener;

use App\Entity\Storage\Storage;
use App\Entity\Storage\Tree;
use App\Entity\Storage\Version;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * Class StorageTreeListener
 *
 * @package App\Listener
 */
class StorageTreeListener
{
    /**
     * @var array
     */
    private $_delete = [];

    /**
     * @param Tree $entity
     * @param LifecycleEventArgs $event
     */
    public function preRemove(Tree $entity, LifecycleEventArgs $event)
    {
        $manager = $event->getObjectManager();

        if ($element = $entity->getStorage()) {
            if ($element->getNodes()->count() === 1) {
                // collecting all children items to remove
                $this->collect($element);
            }
        }

        /* @var Tree[] $children */
        $children = $manager->getRepository('App:Storage\Tree')->getChildren($entity);
        foreach ($children as $child) {
            if ($element = $child->getStorage()) {
                if ($element->getNodes()->count() === 1) {
                    $this->collect($element);
                }
            }
        }
    }

    /**
     * @param Tree $entity
     * @param LifecycleEventArgs $event
     */
    public function postRemove(/* @noinspection PhpUnusedParameterInspection */ Tree $entity, LifecycleEventArgs $event)
    {
        $manager = $event->getObjectManager();

        // Sort array
        ksort($this->_delete);

        foreach ($this->_delete as $className => $items) {
            /* @var \Doctrine\ORM\EntityRepository $repository */
            $repository = $manager->getRepository($className);
            $queryBuilder = $repository->createQueryBuilder('t');
            $queryBuilder
                ->delete()
                ->where($queryBuilder->expr()->in('t.uuid', ':items'))
                ->setParameter('items', $items)
                ->getQuery()
                ->execute();
        }
    }

    /**
     * @param Storage $storage
     */
    private function collect(Storage $storage): void
    {
        $this->_delete['App:Storage\Storage'][] = $storage->getUuid();
        if ($workflow = $storage->getWorkflow()) {
            $this->_delete['App:Workflow'][] = $workflow->getUuid();
        }

        /* @var Version[] $versions */
        $versions = $storage->getVersions()->filter(function (Version $version) {
            return $version->getFile() !== null;
        });

        foreach ($versions as $version) {
            $file = $version->getFile();
            $this->_delete['App:Storage\File'][] = $file->getUuid();
            if ($workflow = $file->getWorkflow()) {
                $this->_delete['App:Workflow'][] = $workflow->getUuid();
            }

//            $this->_delete['File'][] = $file->getUuid();
        }
    }
}