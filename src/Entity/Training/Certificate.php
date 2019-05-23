<?php

namespace App\Entity\Training;

use App\Traits\WorkflowTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="training_certificates")
 * @ORM\Entity()
 */
class Certificate
{
    use WorkflowTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Training\Course")
     * @ORM\JoinColumn(name="course_uuid", referencedColumnName="uuid")
     */
    private $course;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     * @ORM\JoinColumn(name="user_uuid", referencedColumnName="uuid")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account\Account")
     * @ORM\JoinColumn(name="account_uuid", referencedColumnName="uuid")
     */
    private $account;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     * @Assert\Type(type="boolean")
     */
    private $isValid;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expiredAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $fullname;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $position;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $company;
}
