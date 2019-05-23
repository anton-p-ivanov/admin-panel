<?php

namespace App\Entity\Training;

use App\Traits\WorkflowTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="training_courses")
 * @ORM\Entity(repositoryClass="App\Repository\Training\CourseRepository")
 * @ORM\EntityListeners({"App\Listener\WorkflowListener"})
 */
class Course
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
     * @ORM\OneToMany(targetEntity="App\Entity\Training\Lesson", mappedBy="course", orphanRemoval=true, cascade={"persist"})
     * @ORM\OrderBy({"sort": "ASC", "title": "ASC"})
     * @var Collection
     */
    private $lessons;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Training\Test", mappedBy="course", orphanRemoval=true, cascade={"persist"})
     * @var Collection
     */
    private $tests;

    /**
     * Course constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isActive' => true,
            'sort' => 100,
            'lessons' => new ArrayCollection(),
            'tests' => new ArrayCollection()
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Course clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;

        // Unset workflow
        $this->workflow = null;

        // Clone associations
        if ($this->cloneWithAssociations) {
            foreach ($this->lessons as $lesson) {
                $this->addLesson(clone $lesson);
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
     * @return Course
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
     * @return Course
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
     * @return Course
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
     * @return Course
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
     * @return Course
     */
    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return Collection|Lesson[]
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    /**
     * @param Lesson $lesson
     *
     * @return Course
     */
    public function addLesson(Lesson $lesson): self
    {
        $lesson->setCourse($this);

        if (!$this->lessons->contains($lesson)) {
            $this->lessons->add($lesson);
        }

        return $this;
    }

    /**
     * @return Collection|Test[]
     */
    public function getTests(): Collection
    {
        return $this->tests;
    }

    /**
     * @param Test $test
     *
     * @return Course
     */
    public function addTest(Test $test): self
    {
        $test->setCourse($this);

        if (!$this->tests->contains($test)) {
            $this->tests->add($test);
        }

        return $this;
    }
}
