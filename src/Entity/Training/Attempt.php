<?php

namespace App\Entity\Training;

use App\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="training_attempts")
 * @ORM\Entity(repositoryClass="App\Repository\Training\AttemptRepository")
 */
class Attempt
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("boolean")
     */
    private $isValid;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endedAt;
    
    /**
     * @ORM\Column(type="guid")
     */
    private $testUuid;

    /**
     * @ORM\Column(type="guid")
     */
    private $userUuid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Training\Test", inversedBy="attempts")
     * @ORM\JoinColumn(name="test_uuid", referencedColumnName="uuid", nullable=true)
     */
    private $test;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     * @ORM\JoinColumn(name="user_uuid", referencedColumnName="uuid", nullable=true)
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Training\Data", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="training_attempts_data",
     *     joinColumns={@ORM\JoinColumn(name="attempt_uuid", referencedColumnName="uuid")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="data_uuid", referencedColumnName="uuid")}
     * )
     */
    private $data;

    /**
     * Attempt constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isValid' => false,
            'startedAt' => new \DateTime(),
            'data' => new ArrayCollection()
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Attempt clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;
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
    public function getIsValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @param bool $isValid
     *
     * @return Attempt
     */
    public function setIsValid(bool $isValid): self
    {
        $this->isValid = $isValid;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getStartedAt(): ?\DateTime
    {
        return $this->startedAt;
    }

    /**
     * @param \DateTime $startedAt
     *
     * @return Attempt
     */
    public function setStartedAt(\DateTime $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndedAt(): ?\DateTime
    {
        return $this->endedAt;
    }

    /**
     * @param \DateTime $endedAt
     *
     * @return Attempt
     */
    public function setEndedAt(\DateTime $endedAt): self
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTestUuid(): ?string
    {
        return $this->testUuid;
    }

    /**
     * @return Test|null
     */
    public function getTest(): ?Test
    {
        return $this->test;
    }

    /**
     * @param Test|null $test
     *
     * @return Attempt
     */
    public function setTest(?Test $test): self
    {
        $this->test = $test;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUserUuid(): ?string
    {
        return $this->userUuid;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     *
     * @return Attempt
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid === true;
    }

    /**
     * @return Collection|Data[]
     */
    public function getData(): Collection
    {
        return $this->data;
    }

    /**
     * @param Data[] $data
     *
     * @return Attempt
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }
}
