<?php

namespace App\Entity\Storage;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="storage_images")
 * @ORM\Entity()
 */
class Image
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="guid")
     */
    private $file_uuid;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Storage\File")
     * @ORM\JoinColumn(name="file_uuid", referencedColumnName="uuid", onDelete="CASCADE")
     */
    private $file;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $width;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $height;

    /**
     * @return null|string
     */
    public function getFileUuid(): ?string
    {
        return $this->file_uuid;
    }

    /**
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }
}
