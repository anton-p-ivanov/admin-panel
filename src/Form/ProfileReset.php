<?php

namespace App\Form;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProfileReset
 *
 * @package App\Form
 */
class ProfileReset
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $username;

    /**
     * @return null|string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param null|string $username
     * @return ProfileReset
     */
    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }
}