<?php

namespace App\Listener;

use App\Entity\Workflow;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class WorkflowListener
 *
 * @package App\Listener
 */
class WorkflowListener
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var mixed
     */
    private $user;

    /**
     * WorkflowListener constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param Entity $entity
     * @param LifecycleEventArgs $event
     */
    public function prePersist($entity, LifecycleEventArgs $event)
    {
        $this->setWorkflow($entity, $event);
    }

    /**
     * @param Entity $entity
     * @param LifecycleEventArgs $event
     */
    public function postUpdate($entity, LifecycleEventArgs $event)
    {
        $this->setWorkflow($entity, $event);
    }

    /**
     * @param Entity $entity
     * @param LifecycleEventArgs $event
     *
     * @return Workflow
     */
    private function getWorkflow($entity, LifecycleEventArgs $event)
    {
        $workflow = null;

        if (method_exists($entity, 'getWorkflow')) {
            $workflow = $entity->getWorkflow();
        }

        if ($workflow === null) {
            $workflow = new Workflow();
            $workflow->setStatus(
                $event->getObjectManager()
                    ->getRepository('App:WorkflowStatus')
                    ->findOneBy(['isDefault' => true])
            );
        }

        return $workflow;
    }

    /**
     * @param Entity $entity
     * @param LifecycleEventArgs $event
     */
    private function setWorkflow($entity, LifecycleEventArgs $event)
    {
        $token = $this->tokenStorage->getToken();
        if ($token) {
            $this->user = $token->getUser();
        }

        $workflow = $this->getWorkflow($entity, $event);

        if ($this->user) {
            if (method_exists($entity, 'getUuid') && $entity->getUuid() === null) {
                $workflow->setCreated($this->user);
            }
            $workflow->setUpdated($this->user);
        }

        if ($workflow->getUuid()) {
            $workflow->setUpdatedAt(new \DateTime());
            $workflow->setStatus($entity->{'getStatus'}());

            $manager = $event->getObjectManager();
            $manager->persist($workflow);
            $manager->flush();
        }
        else if (method_exists($entity, 'setWorkflow')) {
            $entity->setWorkflow($workflow);

            if ($entity->{'getUuid'}()) {
                $manager = $event->getObjectManager();
                $manager->persist($workflow);
                $manager->flush();
            }
        }
    }
}