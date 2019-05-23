<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="users_checkwords")
 * @ORM\Entity(repositoryClass="App\Repository\UserCheckwordRepository")
 * @ORM\EntityListeners({"App\Listener\UserCheckwordListener"})
 */
class Checkword
{
    /**
     * @var string
     */
    public $nonSecuredCheckword;

    /**
     * @var bool
     */
    public $isNotificationSent = false;

    /**
     * @var array
     */
    public $templates = [];

    /**
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=60, options={"fixed":true}, unique=true)
     */
    private $checkword;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expiredAt;

    /**
     * @ORM\Column(type="boolean", options={"default" = 0})
     */
    private $isExpired;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="userCheckwords")
     * @ORM\JoinColumn(name="user_uuid", referencedColumnName="uuid", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * Checkword constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isExpired' => false,
            'createdAt' => new \DateTime(),
            'expiredAt' => (new \DateTime())->modify('+1 hour'),
            'nonSecuredCheckword' => bin2hex(random_bytes(4))
        ];

        foreach ($defaults as $attribute => $value) {
            $this->$attribute = $value;
        }

        $this->templates = [
            User::SCENARIO_USER_REGISTER => 'USER_REGISTER',
            User::SCENARIO_USER_RESET => 'USER_RESET',
        ];
    }

    /**
     * @return null|string
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getCheckword(): string
    {
        return $this->checkword;
    }

    /**
     * @param string $checkword
     *
     * @return Checkword
     */
    public function setCheckword(string $checkword): self
    {
        $this->checkword = $checkword;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface|null $createdAt
     * @return Checkword
     */
    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getExpiredAt(): ?\DateTimeInterface
    {
        return $this->expiredAt;
    }

    /**
     * @param \DateTimeInterface|null $expiredAt
     * @return Checkword
     */
    public function setExpiredAt(?\DateTimeInterface $expiredAt): self
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsExpired(): ?bool
    {
        return $this->isExpired;
    }

    /**
     * @param bool $isExpired
     * @return Checkword
     */
    public function setIsExpired(bool $isExpired): self
    {
        $this->isExpired = $isExpired;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Checkword
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param string|null $checkword
     *
     * @return bool
     */
    public function isValid(?string $checkword): bool
    {
        $isValid = true;

        if ($checkword) {
            $isValid = $isValid && password_verify($checkword, $this->checkword);
        }

        return $isValid && !$this->isExpired && ($this->expiredAt > new \DateTime());
    }
}
