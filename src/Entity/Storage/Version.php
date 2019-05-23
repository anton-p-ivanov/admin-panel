<?php

namespace App\Entity\Storage;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="storage_versions")
 * @ORM\Entity(repositoryClass="App\Repository\Storage\StorageVersionRepository")
 * @ORM\EntityListeners({"App\Listener\StorageVersionListener"})
 */
class Version
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Storage\File", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="file_uuid", referencedColumnName="uuid", onDelete="CASCADE")
     */
    private $file;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Storage\Storage", inversedBy="versions")
     * @ORM\JoinColumn(name="storage_uuid", referencedColumnName="uuid", onDelete="CASCADE")
     */
    private $storage;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("boolean")
     */
    private $isActive;

    /**
     * Version constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isActive' => true,
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * @return null|string
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return Version
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Storage|null
     */
    public function getStorage(): ?Storage
    {
        return $this->storage;
    }

    /**
     * @param Storage $storage
     *
     * @return Version
     */
    public function setStorage(Storage $storage): self
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @param File $file
     *
     * @return Version
     */
    public function setFile(File $file): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive === true;
    }
}
