<?php

namespace App\Entity\Training;

use App\Traits\WorkflowTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="training_lessons")
 * @ORM\Entity(repositoryClass="App\Repository\Training\LessonRepository")
 * @ORM\EntityListeners({"App\Listener\WorkflowListener"})
 */
class Lesson
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
     * @ORM\Column(type="string", unique=true)
     * @Gedmo\Slug(fields={"title"}, updatable=false, separator="_", style="upper")
     * @Assert\Length(max="255")
     * @Assert\Regex("/^\w+$/")
     */
    private $code;

    /**
     * @ORM\Column(type="integer", options={"default" : 100})
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $sort;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Training\Course", inversedBy="lessons")
     * @ORM\JoinColumn(name="course_uuid", referencedColumnName="uuid", nullable=false)
     * @Assert\Type(type="App\Entity\Training\Course")
     */
    private $course;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Training\Question", mappedBy="lesson", orphanRemoval=true, cascade={"persist"})
     * @var Collection
     */
    private $questions;

    /**
     * Lesson constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isActive' => true,
            'sort' => 100,
            'questions' => new ArrayCollection()
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Lesson clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;

        // Unset workflow
        $this->workflow = null;

        // Clone associations
        if ($this->cloneWithAssociations || $this->course->cloneWithAssociations) {
            foreach ($this->questions as $question) {
                $this->addQuestion(clone $question);
            }
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
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return Lesson
     */
    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
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
     * @return Lesson
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
     * @return Lesson
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
     * @return Lesson
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     *
     * @return Lesson
     */
    public function setSort(int $sort): self
    {
        $this->sort = $sort;

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
     * @return Lesson
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
     * @param Question $question
     *
     * @return Lesson
     */
    public function addQuestion(Question $question): self
    {
        $question->setLesson($this);

        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
        }

        return $this;
    }
}
