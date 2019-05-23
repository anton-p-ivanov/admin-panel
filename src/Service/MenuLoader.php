<?php

namespace App\Service;

use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Class MenuLoader
 * @package App\Service
 */
class MenuLoader
{
    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * MenuLoader constructor.
     * @param DecoderInterface $decoder
     */
    public function __construct(DecoderInterface $decoder)
    {
        $this->decoder = $decoder;
    }

    /**
     * @return mixed
     */
    public function load()
    {
        $data = null;
        $fileName = __DIR__ . '/../Data/Menu.json';

        if (file_exists($fileName)) {
            $data = file_get_contents($fileName);
        }

        return $this->decoder->decode($data, JsonEncoder::FORMAT);
    }
}