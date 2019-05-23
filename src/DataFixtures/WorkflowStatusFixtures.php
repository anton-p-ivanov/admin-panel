<?php

namespace App\DataFixtures;

use App\Entity\WorkflowStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class WorkflowStatusFixtures
 *
 * @package App\DataFixtures
 */
class WorkflowStatusFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $statuses = [
            ['code' => 'DRAFT', 'title' => 'Черновик', 'default' => true],
            ['code' => 'READY', 'title' => 'Готов к публикации', 'default' => false],
            ['code' => 'PUBLISHED', 'title' => 'Опубликован', 'default' => false],
        ];

        foreach ($statuses as $index => $status) {
            $workflowStatus = new WorkflowStatus();
            $attributes = [
                'code' => $status['code'],
                'title' => $status['title'],
                'sort' => ($index + 1) * 100,
                'isDefault' => $status['default']
            ];

            foreach ($attributes as $name => $value) {
                $workflowStatus->{'set' . ucfirst($name)}($value);
            }

            $manager->persist($workflowStatus);
        }

        $manager->flush();
    }
}