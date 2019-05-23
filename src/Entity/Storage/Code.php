<?php

namespace App\Entity\Storage;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="storage_codes")
 * @ORM\Entity()
 */
class Code
{
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Storage\Storage")
     * @ORM\JoinColumn(name="storage_uuid", referencedColumnName="uuid", nullable=true)
     */
    private $storage;

    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=13, options={"fixed": true})
     */
    private $code;
}
