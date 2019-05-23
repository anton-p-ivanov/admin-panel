<?php

namespace App\Security;

use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Security\Core\Encoder\SelfSaltingEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * Class PasswordEncoder
 * @package App\Security
 */
class PasswordEncoder extends BasePasswordEncoder implements SelfSaltingEncoderInterface
{
    const MAX_PASSWORD_LENGTH = 72;

    /**
     * @param string $raw
     * @param string $salt
     *
     * @return string
     *
     * @see \Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder::encodePassword()
     */
    public function encodePassword($raw, $salt)
    {
        if ($this->isPasswordTooLong($raw)) {
            throw new BadCredentialsException('Invalid password.');
        }

        return password_hash($this->password($raw, $salt), PASSWORD_BCRYPT);
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        return !$this->isPasswordTooLong($raw) && password_verify($this->password($raw, $salt), $encoded);
    }

    /**
     * @param string $raw
     * @param string|null $salt
     *
     * @return string
     */
    protected function password(string $raw, ?string $salt): string
    {
        return getenv('APP_SECRET') . $raw . $salt;
    }
}