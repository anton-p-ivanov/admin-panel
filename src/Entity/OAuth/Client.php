<?php

namespace App\Entity\OAuth;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="oauth_clients")
 */
class Client
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
    private $secret;

    /**
     * @ORM\Column(type="json")
     */
    private $redirectUris;

    /**
     * @ORM\Column(type="json")
     */
    private $allowedGrantTypes;

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
    public function getSecret(): ?string
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     *
     * @return Client
     */
    public function setSecret(string $secret): self
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getRedirectUris(): ?array
    {
        return $this->redirectUris;
    }

    /**
     * @param array|null $redirectUris
     *
     * @return Client
     */
    public function setRedirectUris(?array $redirectUris): self
    {
        $this->redirectUris = $redirectUris;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getAllowedGrantTypes(): ?array
    {
        return $this->allowedGrantTypes;
    }

    /**
     * @param array|null $grantTypes
     *
     * @return Client
     */
    public function setAllowedGrantTypes(?array $grantTypes): self
    {
        $this->allowedGrantTypes = $grantTypes;

        return $this;
    }
}
