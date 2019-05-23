<?php

namespace App\Listener;

use App\Entity\Training\Question;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * Class TrainingQuestionListener
 *
 * @package App\Listener
 */
class TrainingQuestionListener
{
    /**
     * @param Question $question
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Question $question, PreUpdateEventArgs $event)
    {
        if ($question->getType() === Question::TYPE_TEXT) {
            foreach ($question->getAnswers() as $answer) {
                $question->removeAnswer($answer);
            }
        }
        else if ($question->getType() === Question::TYPE_SINGLE) {
            $validAnswers = $question->getAnswers(true);

            if (!$validAnswers->isEmpty()) {
                $event->getObjectManager()
                    ->getRepository('App:Training\Answer')
                    ->disableValidAnswers($question, [$validAnswers->first()->getUuid()]);
            }
        }
    }
}