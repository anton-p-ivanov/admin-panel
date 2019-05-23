<?php
/** @noinspection PhpUnusedParameterInspection */

namespace App\Listener;

use App\Entity\Catalog\Element;
use App\Entity\Catalog\Tree;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * Class CatalogTreeListener
 *
 * @package App\Listener
 */
class CatalogTreeListener
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
        // Clear collected before items
        $this->_delete = [];

        $manager = $event->getObjectManager();

        if ($element = $entity->getElement()) {
            if ($element->getNodes()->count() === 1) {
                // collecting all children items to remove
                $this->collect($element);
            }
        }

        /* @var Tree[] $children */
        $children = $manager->getRepository('App:Catalog\Tree')->getChildren($entity);
        foreach ($children as $child) {
            if ($element = $child->getElement()) {
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
    public function postRemove(Tree $entity, LifecycleEventArgs $event)
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
     * @param Element $element
     */
    private function collect(Element $element): void
    {
        $this->_delete['App:Catalog\Element'][] = $element->getUuid();

        if ($price = $element->getPrice()) {
            $this->_delete['App:Price\Price'][] = $price->getUuid();
        }

        if ($workflow = $element->getWorkflow()) {
            $this->_delete['App:Workflow'][] = $workflow->getUuid();
        }
    }
}