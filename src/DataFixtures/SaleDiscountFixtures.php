<?php

namespace App\DataFixtures;

use App\Entity\SalesDiscount;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class SaleDiscountFixtures
 *
 * @package App\DataFixtures
 */
class SaleDiscountFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $discounts = json_decode(file_get_contents(__DIR__.'/../Data/SaleDiscount.json'));

        foreach ($discounts as $discount) {
            $saleDiscount = new SalesDiscount();
            foreach ($discount as $property => $value) {
                $saleDiscount->{'set'.ucfirst($property)}($value);
            }
            $manager->persist($saleDiscount);
        }

        $manager->flush();
    }
}