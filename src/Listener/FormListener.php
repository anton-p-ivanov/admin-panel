<?php

namespace App\Listener;

use App\Entity\Form\Form;
use App\Entity\Mail\Template;
use App\Entity\Mail\Type;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * Class FormListener
 *
 * @package App\Listener
 */
class FormListener
{
    /**
     * @var array
     */
    private $_delete = [];

    /**
     * @param Form $entity
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Form $entity, LifecycleEventArgs $event)
    {
        $type = new Type();
        $attributes = [
            'code' => 'MAIL_TYPE_FORM_',
            'title' => mb_substr('Шаблоны формы "' . $entity->getTitle(), 0, 254) . '"'
        ];

        foreach ($attributes as $attribute => $value) {
            $type->{'set' . ucfirst($attribute)}($value);
        }

        $template = new Template();
        $attributes = [
            'type' => $type,
            'code' => 'MAIL_TEMPLATE_FORM_',
            'sender' => '{{SITE_EMAIL}}',
            'recipient' => getenv('MAILER_RECIPIENT'),
            'subject' => mb_substr('Заполнена веб-форма "' . $entity->getTitle(), 0, 200) . '"',
            'textBody' => file_get_contents(__DIR__ . "/../Data/FormMailTemplate.txt"),
            'sites' => $event->getObjectManager()->getRepository('App:Site')->findBy(['isActive' => true])
        ];

        foreach ($attributes as $attribute => $value) {
            $template->{'set' . ucfirst($attribute)}($value);
        }

        $entity->setMailTemplate($template);
    }

    /**
     * @param Form $entity
     */
    public function preRemove(Form $entity)
    {
        // collecting all children items to remove
        $this->collect($entity);
    }

    /**
     * @param Form $entity
     * @param LifecycleEventArgs $event
     */
    public function postRemove(/** @noinspection PhpUnusedParameterInspection */Form $entity, LifecycleEventArgs $event)
    {
        $manager = $event->getObjectManager();

        foreach ($this->_delete['App:Template'] as $entity) {
            $manager->remove($entity);
        }

        $manager->flush();

        $manager->remove($this->_delete['App:Type']);
        $manager->flush();
    }

    /**
     * @param Form $form
     */
    private function collect(Form $form): void
    {
        $mailType = $form->getMailTemplate()->getType();
        if ($mailType) {
            $this->_delete['App:Template'] = $mailType->getTemplates()->toArray();
            $this->_delete['App:Type'] = $mailType;
        }
    }
}