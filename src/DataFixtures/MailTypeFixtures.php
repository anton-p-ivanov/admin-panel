<?php

namespace App\DataFixtures;

use App\Entity\Mail\Type;
use App\Entity\Workflow;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class MailTypeFixtures
 *
 * @package App\DataFixtures
 */
class MailTypeFixtures extends Fixture implements DependentFixtureInterface
{
    public const MAIL_TYPE_REFERENCE = 'mailType';

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $workflow = new Workflow();
        $workflow->setIsDeleted(false);
        $workflow->setStatus(
            $manager
                ->getRepository('App:WorkflowStatus')
                ->findOneBy(['isDefault' => true])
        );

        $attributes = [
            'code' => 'SYSTEM',
            'title' => 'System messages',
            'workflow' => $workflow
        ];

        $mailType = new Type();
        foreach ($attributes as $name => $value) {
            $mailType->{'set' . ucfirst($name)}($value);
        }

        $manager->persist($mailType);
        $manager->flush();

        $this->addReference(self::MAIL_TYPE_REFERENCE, $mailType);
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            WorkflowStatusFixtures::class,
        ];
    }
}