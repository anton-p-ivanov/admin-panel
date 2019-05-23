<?php

namespace App\DataFixtures;

use App\Entity\Mail\Template;
use App\Entity\Mail\Type;
use App\Entity\Workflow;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class MailTemplateFixtures
 *
 * @package App\DataFixtures
 */
class MailTemplateFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /* @var $mailType Type */
        $mailType = $this->getReference(MailTypeFixtures::MAIL_TYPE_REFERENCE);

        $sites = $manager->getRepository('App:Site')->findAll();
        $templates = json_decode(file_get_contents(__DIR__ . '/../Data/Templates.json'));

        foreach ($templates as $attributes) {
            $mailTemplate = new Template();
            foreach ($attributes as $name => $value) {
                $mailTemplate->{'set' . ucfirst($name)}($value);
            }

            $mailTemplate->setType($mailType);
            $mailTemplate->setTextBody(file_get_contents(__DIR__ . "/Mail/Text/" . $attributes->code));
            $mailTemplate->setSites($sites);
            $mailTemplate->setWorkflow($this->getWorkflow($manager));

            $manager->persist($mailTemplate);
        }

        $manager->flush();
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            MailTypeFixtures::class
        ];
    }

    /**
     * @param ObjectManager $manager
     *
     * @return Workflow
     */
    private function getWorkflow(ObjectManager $manager)
    {
        $workflow = new Workflow();
        $workflow->setIsDeleted(false);
        $workflow->setStatus(
            $manager
                ->getRepository('App:WorkflowStatus')
                ->findOneBy(['isDefault' => true])
        );

        return $workflow;
    }
}