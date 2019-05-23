<?php

namespace App\Entity\Training;

use App\Traits\WorkflowTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="training_tests")
 * @ORM\Entity(repositoryClass="App\Repository\Training\TestRepository")
 * @ORM\EntityListeners({"App\Listener\WorkflowListener"})
 */
class Test
{
    use WorkflowTrait;

    /**
     * @var bool
     */
    public $cloneWithAssociations = false;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Length(max="1000")
     */
    private $description;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     * @Assert\Type("boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("boolean")
     */
    private $isRandomQuestions;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("boolean")
     */
    private $isRandomAnswers;

    /**
     * @ORM\Column(type="integer", length=1)
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(0)
     */
    private $limitAttempts;

    /**
     * @ORM\Column(type="integer", length=1)
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(0)
     */
    private $limitTime;

    /**
     * @ORM\Column(type="integer", length=1)
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(0)
     * @Assert\LessThanOrEqual(100)
     */
    private $limitPercent;

    /**
     * @ORM\Column(type="integer", length=1)
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(0)
     */
    private $limitValue;

    /**
     * @ORM\Column(type="integer", length=1)
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(0)
     */
    private $limitQuestions;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Training\Course", inversedBy="tests")
     * @ORM\JoinColumn(name="course_uuid", referencedColumnName="uuid", nullable=false)
     * @Assert\Type(type="App\Entity\Training\Course")
     */
    private $course;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Training\Question")
     * @ORM\JoinTable(
     *     name="training_tests_questions",
     *     joinColumns={@ORM\JoinColumn(name="test_uuid", referencedColumnName="uuid")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="question_uuid", referencedColumnName="uuid")}
     * )
     */
    private $questions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Training\Attempt", mappedBy="test", orphanRemoval=true, cascade={"persist"})
     * @var Collection
     */
    private $attempts;

    /**
     * Test constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isActive' => true,
            'isRandomQuestions' => false,
            'isRandomAnswers' => false,
            'limitAttempts' => 0,
            'limitTime' => 0,
            'limitPercent' => 100,
            'limitValue' => 0,
            'limitQuestions' => 0,
            'questions' => new ArrayCollection(),
            'attempts' => new ArrayCollection()
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Test clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;

        // Unset workflow
        $this->workflow = null;

        // Clone associations
        if (!$this->cloneWithAssociations) {
            $this->questions = new ArrayCollection();
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
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Test
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     *
     * @return Test
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return Test
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsRandomQuestions(): bool
    {
        return $this->isRandomQuestions;
    }

    /**
     * @param bool $isRandomQuestions
     *
     * @return Test
     */
    public function setIsRandomQuestions(bool $isRandomQuestions): self
    {
        $this->isRandomQuestions = $isRandomQuestions;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsRandomAnswers(): bool
    {
        return $this->isRandomAnswers;
    }

    /**
     * @param bool $isRandomAnswers
     *
     * @return Test
     */
    public function setIsRandomAnswers(bool $isRandomAnswers): self
    {
        $this->isRandomAnswers = $isRandomAnswers;

        return $this;
    }

    /**
     * @return integer
     */
    public function getLimitAttempts(): int
    {
        return $this->limitAttempts;
    }

    /**
     * @param integer $limitAttempts
     *
     * @return Test
     */
    public function setLimitAttempts(int $limitAttempts): self
    {
        $this->limitAttempts = $limitAttempts;

        return $this;
    }

    /**
     * @return integer
     */
    public function getLimitTime(): int
    {
        return $this->limitTime;
    }

    /**
     * @param integer $limitTime
     *
     * @return Test
     */
    public function setLimitTime(int $limitTime): self
    {
        $this->limitTime = $limitTime;

        return $this;
    }

    /**
     * @return integer
     */
    public function getLimitPercent(): int
    {
        return $this->limitPercent;
    }

    /**
     * @param integer $limitPercent
     *
     * @return Test
     */
    public function setLimitPercent(int $limitPercent): self
    {
        $this->limitPercent = $limitPercent;

        return $this;
    }

    /**
     * @return integer
     */
    public function getLimitValue(): int
    {
        return $this->limitValue;
    }

    /**
     * @param integer $limitValue
     *
     * @return Test
     */
    public function setLimitValue(int $limitValue): self
    {
        $this->limitValue = $limitValue;

        return $this;
    }

    /**
     * @return integer
     */
    public function getLimitQuestions(): int
    {
        return $this->limitQuestions;
    }

    /**
     * @param integer $limitQuestions
     *
     * @return Test
     */
    public function setLimitQuestions(int $limitQuestions): self
    {
        $this->limitQuestions = $limitQuestions;

        return $this;
    }

    /**
     * @return Course|null
     */
    public function getCourse(): ?Course
    {
        return $this->course;
    }

    /**
     * @param Course $course
     *
     * @return Test
     */
    public function setCourse(Course $course): self
    {
        $this->course = $course;

        return $this;
    }

    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    /**
     * @param Question[] $questions
     *
     * @return Test
     */
    public function setQuestions(array $questions): self
    {
        $this->questions = $questions;

        return $this;
    }

    /**
     * @return Collection|Attempt[]
     */
    public function getAttempts(): Collection
    {
        return $this->attempts;
    }

    /**
     * @param Attempt $attempt
     *
     * @return Test
     */
    public function addAttempt(Attempt $attempt): self
    {
        $attempt->setTest($this);

        if (!$this->attempts->contains($attempt)) {
            $this->attempts->add($attempt);
        }

        return $this;
    }
}
