<?php

namespace App\Entity\Training;

use App\Traits\WorkflowTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="training_questions")
 * @ORM\Entity(repositoryClass="App\Repository\Training\QuestionRepository")
 * @ORM\EntityListeners({
 *     "App\Listener\TrainingQuestionListener",
 *     "App\Listener\WorkflowListener"
 * })
 */
class Question
{
    /**
     * Question types constants
     */
    const TYPE_DEFAULT = 'S';
    const TYPE_SINGLE = 'S';
    const TYPE_MULTIPLE = 'M';
    const TYPE_TEXT = 'T';

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
     * @ORM\Column(type="string", length=500)
     * @Assert\NotBlank()
     * @Assert\Length(max="500")
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
     * @ORM\Column(type="string", length=1, options={"fixed": true})
     * @Assert\Choice(callback="getTypes")
     */
    private $type;

    /**
     * @ORM\Column(type="integer", options={"default" : 10})
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $value;

    /**
     * @ORM\Column(type="integer", options={"default" : 100})
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $sort;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Training\Lesson", inversedBy="questions")
     * @ORM\JoinColumn(name="lesson_uuid", referencedColumnName="uuid", nullable=false)
     * @Assert\Type(type="App\Entity\Training\Lesson")
     */
    private $lesson;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Training\Answer", mappedBy="question", orphanRemoval=true, cascade={"persist"})
     * @var Collection
     */
    private $answers;

    /**
     * Question constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isActive' => true,
            'sort' => 100,
            'value' => 10,
            'type' => self::TYPE_DEFAULT,
            'answers' => new ArrayCollection()
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Question clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;

        // Unset workflow
        $this->workflow = null;

        // Clone associations
        if ($this->cloneWithAssociations
            || $this->getLesson()->cloneWithAssociations
            || $this->getLesson()->getCourse()->cloneWithAssociations
        ) {
            foreach ($this->answers as $answer) {
                $this->addAnswer(clone $answer);
            }
        }
    }

    /**
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            'training.question.types.single' => self::TYPE_SINGLE,
            'training.question.types.multiple' => self::TYPE_MULTIPLE,
            'training.question.types.text' => self::TYPE_TEXT,
        ];
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
     * @return Question
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
     * @return Question
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
     * @return Question
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
     * @return Question
     */
    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param int $value
     *
     * @return Question
     */
    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return Lesson|null
     */
    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    /**
     * @param Lesson $lesson
     *
     * @return Question
     */
    public function setLesson(Lesson $lesson): self
    {
        $this->lesson = $lesson;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string 
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Question
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param bool $validOnly
     *
     * @return Collection|Answer[]
     */
    public function getAnswers(bool $validOnly = false): Collection
    {
        $answers = $this->answers;

        if ($validOnly) {
            return $this->filterValidAnswers();
        }

        return $answers;
    }

    /**
     * @return Collection
     */
    private function filterValidAnswers()
    {
        return $this->answers->filter(function (Answer $answer) {
            return $answer->isValid();
        });
    }

    /**
     * @param Answer $answer
     *
     * @return Question
     */
    public function addAnswer(Answer $answer): self
    {
        $answer->setQuestion($this);

        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
        }

        return $this;
    }

    /**
     * @param Answer $answer
     *
     * @return Question
     */
    public function removeAnswer(Answer $answer): self
    {
        // Unset association
        $answer->setQuestion(null);

        // Remove answer from collection
        $this->answers->removeElement($answer);

        return $this;
    }

    /**
     * @param bool $validOnly
     *
     * @return bool
     */
    public function hasAnswers(bool $validOnly = false): bool
    {
        $answers = $this->answers;

        if ($validOnly) {
            $answers = $this->filterValidAnswers();
        }

        return $answers->count() > 0;
    }
}
