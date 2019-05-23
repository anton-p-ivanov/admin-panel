<?php

namespace App\Listener;

use App\Entity\Form\Status;
use App\Entity\Mail\Template;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class FormStatusListener
 *
 * @package App\Listener
 */
class FormStatusListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * DeleteEntity constructor.
     *
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param Status $status
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Status $status, LifecycleEventArgs $event)
    {
        if ($status->getIsDefault() === true) {
            $this->disableDefaultStatus($status, $event->getObjectManager());
        }

        $this->insertMailTemplate($status, $event);
    }

    /**
     * @param Status $status
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Status $status, PreUpdateEventArgs $event)
    {
        if ($status->getIsDefault() === true) {
            $this->disableDefaultStatus($status, $event->getObjectManager());
        }
    }

    /**
     * @param Status $status
     * @param LifecycleEventArgs $event
     */
    public function preRemove(Status $status, LifecycleEventArgs $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($status->getIsDefault() === true && $request->attributes->get('_route') === 'form_status_delete') {
            // Detach entity to prevent it removal
            $event->getObjectManager()->detach($status);

            // Send error state with response
            $response = new JsonResponse(['error' => 'Статус веб-формы с параметром "По-умолчанию" не может быть удален.'], 400);
            $response->send();
        }
    }

    /**
     * @param Status $status
     * @param ObjectManager $manager
     */
    private function disableDefaultStatus(Status $status, ObjectManager $manager)
    {
        $manager->getRepository('App:Form\Status')->disableDefaultStatus($status->getForm());
    }

    /**
     * @param Status $entity
     * @param LifecycleEventArgs $event
     */
    private function insertMailTemplate(Status $entity, LifecycleEventArgs $event)
    {
        $template = new Template();
        $attributes = [
            'type' => $entity->getForm()->getMailTemplate()->getType(),
            'code' => 'MAIL_TEMPLATE_FORM_STATUS_',
            'sender' => '{{SITE_EMAIL}}',
            'recipient' => getenv('MAILER_RECIPIENT'),
            'subject' => mb_substr('Изменён статус вашей заявки "' . $entity->getForm()->getTitle(), 0, 200) . '"',
            'textBody' => file_get_contents(__DIR__ . "/../Data/FormStatusMailTemplate.txt"),
            'sites' => $event->getObjectManager()->getRepository('App:Site')->findBy(['isActive' => true])
        ];

        foreach ($attributes as $attribute => $value) {
            $template->{'set' . ucfirst($attribute)}($value);
        }

        $entity->setMailTemplate($template);
    }
}