<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="users_accounts")
 * @ORM\Entity(repositoryClass="App\Repository\UserAccountRepository")
 */
class Account
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    private $position;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="accounts")
     * @ORM\JoinColumn(name="user_uuid", referencedColumnName="uuid", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account\Account")
     * @ORM\JoinColumn(name="account_uuid", referencedColumnName="uuid", nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank()
     */
    private $account;

    /**
     * Account clone.
     */
    public function __clone()
    {
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
     * @return null|string
     */
    public function getPosition(): ?string
    {
        return $this->position;
    }

    /**
     * @param string|null $position
     *
     * @return Account
     */
    public function setPosition(?string $position): self
    {
        $this->position = $position ? $position : '';

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
     *
     * @return Account
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \App\Entity\Account\Account|null
     */
    public function getAccount(): ?\App\Entity\Account\Account
    {
        return $this->account;
    }

    /**
     * @param \App\Entity\Account\Account|null $account
     *
     * @return Account
     */
    public function setAccount(?\App\Entity\Account\Account $account): self
    {
        $this->account = $account;

        return $this;
    }
}
