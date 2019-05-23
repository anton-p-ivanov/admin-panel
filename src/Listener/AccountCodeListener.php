<?php

namespace App\Listener;

use App\Entity\Account\Code;
use App\Service\Mail;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class AccountListener
 *
 * @package App\Listener
 */
class AccountCodeListener
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var Mail
     */
    private $mailer;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * AccountCodeListener constructor.
     *
     * @param Mail $mailer
     * @param TokenStorageInterface $tokenStorage
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(Mail $mailer, TokenStorageInterface $tokenStorage, UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
        $this->mailer = $mailer;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param Code $accountCode
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Code $accountCode, LifecycleEventArgs $event)
    {
        /* @var $user \Symfony\Component\Security\Core\User\UserInterface */
        $user = $this->tokenStorage->getToken()->getUser();
        $accountCode->setCode($this->encoder->encodePassword($user, $accountCode->nonEncodedAccountCode));

        $event
            ->getObjectManager()
            ->getRepository('App:Account\Code')
            ->expireAccountCodes($accountCode->getAccount());
    }

    /**
     * @param Code $accountCode
     */
    public function postPersist(Code $accountCode)
    {
        $params = [
            'ACCOUNT_TITLE' => $accountCode->getAccount()->getTitle(),
            'CODE' => $accountCode->nonEncodedAccountCode,
            'CLIENT_ID' => 'ADMIN'
        ];

        // Notify account owner about registration code update.
        if (!$accountCode->isNotificationSent) {
            $accountCode->isNotificationSent = $this->mailer
                ->template('ACCOUNT_REGISTRATION_CODE_UPDATED')
                ->params($params)
                ->send();
        }

        // Notify site administrator about registration code update.
        $this->mailer
            ->template('ACCOUNT_REGISTRATION_CODE_UPDATED_ADMIN')
            ->params($params)
            ->send();
    }
}