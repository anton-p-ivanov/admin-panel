<?php

namespace App\Listener;

use App\Entity\User\User;
use App\Entity\User\Checkword;
use App\Entity\User\Password;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserPasswordListener
 *
 * @package App\Listener
 */
class UserPasswordListener
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * UserCheckwordListener constructor.
     *
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param Password $userPassword
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Password $userPassword, LifecycleEventArgs $event)
    {
        // Encode password
        $userPassword->setPassword($this->encoder->encodePassword(
            $userPassword->getUser(),
            $userPassword->getPassword())
        );

        // Expire all previous user passwords
        $event
            ->getObjectManager()
            ->getRepository('App:User\Password')
            ->expireUserPasswords($userPassword->getUser());

        // Expire all previous user checkwords
        $event
            ->getObjectManager()
            ->getRepository('App:User\Checkword')
            ->expireUserCheckwords($userPassword->getUser());
    }

    /**
     * @param Password $userPassword
     * @param LifecycleEventArgs $event
     */
    public function postPersist(Password $userPassword, LifecycleEventArgs $event)
    {
        $user = $userPassword->getUser();

        if ($user->scenario === User::SCENARIO_USER_REGISTER) {
            $userCheckword = new Checkword();
            $userCheckword->setUser($user);

            $manager = $event->getObjectManager();
            $manager->persist($userCheckword);
            $manager->flush();
        }
    }
}