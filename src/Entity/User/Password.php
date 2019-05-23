<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="users_passwords")
 * @ORM\Entity(repositoryClass="App\Repository\UserPasswordRepository")
 * @ORM\EntityListeners({"App\Listener\UserPasswordListener"})
 */
class Password
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=60, options={"fixed" = true})
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=20, options={"fixed" = true})
     */
    private $salt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expiredAt;

    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $isExpired;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="userPasswords", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="user_uuid", referencedColumnName="uuid", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * Password constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isExpired' => false,
            'salt' => bin2hex(random_bytes(10)),
            'createdAt' => new \DateTime(),
            'expiredAt' => (new \DateTime())->modify('+1 year')
        ];

        foreach ($defaults as $attribute => $value) {
            $this->$attribute = $value;
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
     * @return null|string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return Password
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * @param string $salt
     *
     * @return Password
     */
    public function setSalt(string $salt): self
    {
        $this->salt = $salt;

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
     * @return Password
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
     * @return Password
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
     * @return Password
     */
    public function setIsExpired(bool $isExpired): self
    {
        $this->isExpired = $isExpired;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Password
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->isExpired || ($this->expiredAt <= new \DateTime());
    }
}
