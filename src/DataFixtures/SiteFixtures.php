<?php

namespace App\DataFixtures;

use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Class SiteFixtures
 *
 * @package App\DataFixtures
 */
class SiteFixtures extends Fixture implements DependentFixtureInterface
{
    public const SITE_REFERENCE = 'site';

    /**
     * @var array
     */
    private $_sites;

    /**
     * SiteFixtures constructor.
     *
     * @param DecoderInterface $encoder
     */
    public function __construct(DecoderInterface $encoder)
    {
        $this->_sites = $encoder->decode(file_get_contents(__DIR__ . '/../Data/Sites.json'), JsonEncoder::FORMAT);
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->_sites as $attributes) {
            $site = new Site();
            foreach ($attributes as $name => $value) {
                $site->{'set' . ucfirst($name)}($value);
            }

            $manager->persist($site);
            $manager->flush();
        }
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