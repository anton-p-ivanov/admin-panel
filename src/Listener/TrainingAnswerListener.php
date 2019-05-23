<?php

namespace App\Listener;

use App\Entity\Training\Answer;
use App\Entity\Training\Question;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class TrainingAnswerListener
 *
 * @package App\Listener
 */
class TrainingAnswerListener
{
    /**
     * @param Answer $answer
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Answer $answer, LifecycleEventArgs $event)
    {
        if ($answer->isValid() && $answer->getQuestion()->getType() === Question::TYPE_SINGLE) {
            $this->disableValidAnswers($answer->getQuestion(), $event->getObjectManager());
        }
    }

    /**
     * @param Answer $answer
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Answer $answer, PreUpdateEventArgs $event)
    {
        if ($answer->isValid() && $answer->getQuestion()->getType() === Question::TYPE_SINGLE) {
            $this->disableValidAnswers($answer->getQuestion(), $event->getObjectManager());
        }
    }

    /**
     * @param Answer $answer
     * @param LifecycleEventArgs $event
     */
    public function preRemove(Answer $answer, LifecycleEventArgs $event)
    {
        $question = $answer->getQuestion();

        if ($answer->isValid() && ($question && $question->getAnswers(true)->count() === 1)) {
            // Detach entity to prevent it removal
            $event->getObjectManager()->detach($answer);

            // Send error state with response
            $response = new JsonResponse(['error' => 'Нельзя удалить единственный правильный ответ на вопрос.'], 400);
            $response->send();
        }
    }

    /**
     * @param Question $question
     * @param ObjectManager $manager
     */
    private function disableValidAnswers(Question $question, ObjectManager $manager)
    {
        $manager->getRepository('App:Training\Answer')->disableValidAnswers($question);
    }
}