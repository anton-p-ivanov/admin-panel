<?php

namespace App\Listener;

use App\Entity\WorkflowStatus;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class WorkflowStatusListener
 *
 * @package App\Listener
 */
class WorkflowStatusListener
{
    /**
     * @param WorkflowStatus $workflowStatus
     * @param LifecycleEventArgs $event
     */
    public function prePersist(WorkflowStatus $workflowStatus, LifecycleEventArgs $event)
    {
        if ($workflowStatus->getIsDefault() === true) {
            $this->disableDefaultStatus($event->getObjectManager());
        }
    }

    /**
     * @param WorkflowStatus $status
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(WorkflowStatus $status, PreUpdateEventArgs $event)
    {
        if ($status->getIsDefault() === true) {
            $this->disableDefaultStatus($event->getObjectManager());
        }
    }

    /**
     * @param WorkflowStatus $status
     * @param LifecycleEventArgs $event
     */
    public function preRemove(WorkflowStatus $status, LifecycleEventArgs $event)
    {
        if ($status->getIsDefault() === true) {
            // Detach entity to prevent it removal
            $event->getObjectManager()->detach($status);

            // Send error state with response
            $response = new JsonResponse(['error' => 'Статус документооборота с параметром "По-умолчанию" не может быть удален.'], 400);
            $response->send();
        }
    }

    /**
     * @param ObjectManager $manager
     */
    private function disableDefaultStatus(ObjectManager $manager)
    {
        $manager->getRepository('App:WorkflowStatus')->disableDefaultStatus();
    }
}