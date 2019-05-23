<?php

namespace App\Entity\Training;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="training_answers")
 * @ORM\Entity(repositoryClass="App\Repository\Training\AnswerRepository")
 * @ORM\EntityListeners({"App\Listener\TrainingAnswerListener"})
 */
class Answer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Length(max="1000")
     */
    private $answer;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     * @Assert\Type("boolean")
     */
    private $isValid;

    /**
     * @ORM\Column(type="integer", options={"default" : 100})
     * @Assert\Type("numeric")
     * @Assert\GreaterThanOrEqual(value="0")
     */
    private $sort;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Training\Question", inversedBy="answers")
     * @ORM\JoinColumn(name="question_uuid", referencedColumnName="uuid", nullable=false)
     * @Assert\Type(type="App\Entity\Training\Question")
     */
    private $question;

    /**
     * Question constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isValid' => false,
            'sort' => 100,
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Answer clone.
     */
    public function __clone()
    {
        // Unset primary key
        $this->uuid = null;
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
    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    /**
     * @param string $answer
     *
     * @return Answer
     */
    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @param bool $isValid
     *
     * @return Answer
     */
    public function setIsValid(bool $isValid): self
    {
        $this->isValid = $isValid;

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
     * @return Answer
     */
    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return Question|null
     */
    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    /**
     * @param Question|null $question
     *
     * @return Answer
     */
    public function setQuestion(?Question $question): self
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid === true;
    }
}
