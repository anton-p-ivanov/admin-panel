<?php

namespace App\Listener;

use App\Entity\User\Checkword;
use App\Service\Mail;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserCheckwordListener
 *
 * @package App\Listener
 */
class UserCheckwordListener
{
    /**
     * @var Mail
     */
    private $mailer;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * UserCheckwordListener constructor.
     *
     * @param Mail $mailer
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(Mail $mailer, UserPasswordEncoderInterface $encoder)
    {
        $this->mailer = $mailer;
        $this->encoder = $encoder;
    }

    /**
     * @param Checkword $userCheckword
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Checkword $userCheckword, LifecycleEventArgs $event)
    {
        $userCheckword->setCheckword($this->encoder->encodePassword(
            $userCheckword->getUser(),
            $userCheckword->nonSecuredCheckword
        ));

        $event
            ->getObjectManager()
            ->getRepository('App:User\Checkword')
            ->expireUserCheckwords($userCheckword->getUser());
    }

    /**
     * @param Checkword $userCheckword
     */
    public function postPersist(Checkword $userCheckword)
    {
        // Skip user notification
        if ($userCheckword->isNotificationSent)
        {
            return;
        }

        $params = [
            'USER_EMAIL' => $userCheckword->getUser()->getUsername(),
            'USER_CHECKWORD' => $userCheckword->nonSecuredCheckword,
            'CLIENT_ID' => 'ADMIN'//$request->headers->get('client-id')
        ];

        $userCheckword->isNotificationSent = $this->mailer
            ->template($userCheckword->templates[$userCheckword->getUser()->scenario])
            ->params($params)
            ->send();
    }
}