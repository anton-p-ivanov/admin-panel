<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class SizeFilter
 * @package App\Twig
 */
class SizeFilter extends AbstractExtension
{
    /**
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('size', [$this, 'sizeFilter'])
        ];
    }

    /**
     * @param mixed $value
     * @param int $decimals
     *
     * @return string
     */
    public function sizeFilter($value, $decimals = 2)
    {
        $formatBase = 1024;
        $maxPosition = 4;
        $position = 0;

        do {
            if (abs($value) < $formatBase) {
                break;
            }
            $value /= $formatBase;
            $position++;
        } while ($position < $maxPosition + 1);

        $value = number_format($value, $decimals);

        switch ($position) {
            case 0:
                return $value . ' байт';
            case 1:
                return $value . ' Кбайт';
            case 2:
                return $value . ' Мбайт';
            case 3:
                return $value . ' Гбайт';
            case 4:
                return $value . ' Тбайт';
            default:
                return $value . ' Пбайт';
        }
    }
}