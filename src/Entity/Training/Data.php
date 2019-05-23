<?php

namespace App\Entity\Training;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="training_data")
 * @ORM\Entity()
 */
class Data
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="guid")
     */
    private $questionUuid;

    /**
     * @ORM\Column(type="guid")
     */
    private $answerUuid;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("boolean")
     */
    private $isValid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Training\Question")
     * @ORM\JoinColumn(name="question_uuid", referencedColumnName="uuid")
     */
    private $question;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Training\Answer")
     * @ORM\JoinColumn(name="answer_uuid", referencedColumnName="uuid")
     */
    private $answer;

    /**
     * Data constructor.
     */
    public function __construct()
    {
        $defaults = [
            'isValid' => false,
        ];

        foreach ($defaults as $property => $value) {
            $this->$property = $value;
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
     * @return bool
     */
    public function getIsValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @param bool $isValid
     *
     * @return Data
     */
    public function setIsValid(bool $isValid): self
    {
        $this->isValid = $isValid;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuestionUuid(): string
    {
        return $this->questionUuid;
    }

    /**
     * @return Question
     */
    public function getQuestion(): Question
    {
        return $this->question;
    }

    /**
     * @param Question $question
     *
     * @return Data
     */
    public function setQuestion(Question $question): self
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return string
     */
    public function getAnswerUuid(): string
    {
        return $this->answerUuid;
    }

    /**
     * @return Answer
     */
    public function getAnswer(): Answer
    {
        return $this->answer;
    }

    /**
     * @param Answer $answer
     *
     * @return Data
     */
    public function setAnswer(Answer $answer): self
    {
        $this->answer = $answer;

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
