<?php

namespace App\Entity\Storage;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="storage_requests")
 * @ORM\Entity()
 */
class Request
{
    /**
     * Request has been issued by user
     */
    const STATUS_WAITING = 'W';
    /**
     * Request has been granted by administrator
     */
    const STATUS_GRANTED = 'G';
    /**
     * Request has been denied by administrator
     */
    const STATUS_DENIED = 'D';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Storage\Storage")
     * @ORM\JoinColumn(name="storage_uuid", referencedColumnName="uuid")
     * @Assert\NotBlank()
     */
    private $storage;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     * @ORM\JoinColumn(name="user_uuid", referencedColumnName="uuid")
     * @Assert\NotBlank()
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=1, options={"fixed": true})
     * @Assert\NotBlank()
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Type(type="\DateTime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Type(type="\DateTime")
     */
    private $expiredAt;
}
