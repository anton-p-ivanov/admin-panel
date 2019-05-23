<?php

namespace App\DataFixtures;

use App\Entity\PartnershipStatus;
use App\Entity\PartnershipDiscount;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class PartnershipStatusFixtures
 *
 * @package App\DataFixtures
 */
class PartnershipStatusFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $statuses = json_decode(file_get_contents(__DIR__.'/../Data/PartnershipStatuses.json'));

        foreach ($statuses as $status) {
            $partnershipStatus = new PartnershipStatus();
            foreach ($status as $property => $value) {
                $partnershipStatus->{'set'.ucfirst($property)}($value);
            }

            $this->setDiscounts($partnershipStatus, $manager);
            $manager->persist($partnershipStatus);
        }

        $manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            SaleDiscountFixtures::class
        ];
    }

    /**
     * @param PartnershipStatus $status
     * @param ObjectManager $manager
     */
    private function setDiscounts(PartnershipStatus $status, ObjectManager $manager)
    {
        $discounts = $manager->getRepository('App:SalesDiscount')->findAll();

        foreach ($discounts as $discount) {
            $properties = [
                'value' => random_int(0, 50) / 100,
                'discount' => $discount
            ];
            $statusDiscount = new PartnershipDiscount();
            foreach ($properties as $property => $value) {
                $statusDiscount->{'set' . ucfirst($property)}($value);
            }

            $status->addDiscount($statusDiscount);
        }
    }
}