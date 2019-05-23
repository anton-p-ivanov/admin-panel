<?php

namespace App\Form;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProfilePassword
 *
 * @package App\Form
 */
class ProfilePassword
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $username;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $checkword;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $password;

    /**
     * @var string
     * @---Assert\IdenticalTo('password')
     */
    private $password_repeat;

    /**
     * ProfilePassword constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $attribute => $value) {
            $methodName = 'set' . $attribute;
            if (method_exists($this, $methodName)) {
                $this->$methodName($value);
            }
        }
    }

    /**
     * @return null|string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param null|string $username
     *
     * @return ProfilePassword
     */
    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCheckword(): ?string
    {
        return $this->checkword;
    }

    /**
     * @param null|string $checkword
     *
     * @return ProfilePassword
     */
    public function setCheckword(?string $checkword): self
    {
        $this->checkword = $checkword;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     *
     * @return ProfilePassword
     */
    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPasswordRepeat(): ?string
    {
        return $this->password_repeat;
    }

    /**
     * @param string|null $password_repeat
     *
     * @return ProfilePassword
     */
    public function setPasswordRepeat(?string $password_repeat): self
    {
        $this->password_repeat = $password_repeat;

        return $this;
    }
}