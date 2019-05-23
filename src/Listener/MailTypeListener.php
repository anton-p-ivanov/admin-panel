<?php

namespace App\Listener;

use App\Entity\Mail\Type;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class MailTypeListener
 *
 * @package App\Listener
 */
class MailTypeListener
{
    /**
     * @param Type $type
     * @param LifecycleEventArgs $event
     */
    public function preRemove(Type $type, LifecycleEventArgs $event)
    {
        $message = 'Тип со связаными почтовыми шаблонами не можеть быть удалён.';

        if (!$type->getTemplates()->isEmpty()) {
            // Detach entity to prevent it removal
            $event->getObjectManager()->detach($type);

            // Send error state with response
            $response = new JsonResponse(['error' => $message], 400);
            $response->send();
        }
    }
}