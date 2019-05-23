<?php

namespace App\Controller\Training;

use App\Entity\Training\Answer;
use App\Entity\Training\Lesson;
use App\Entity\Training\Question;
use App\Form\Training\QuestionType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/training/questions")
 */
class QuestionsController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/lesson/{uuid}/{page<\d+>?1}", name="training_question_filter", methods="GET")
     *
     * @param Http\Request $request
     * @param Lesson $lesson
     * @param int $page
     *
     * @return Http\Response
     */
    public function filter(Http\Request $request, Lesson $lesson, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Question::class)
            ->search($lesson);

        $view = 'training/questions/index.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'training/questions/_index.html.twig';
        }

        return $this->render($view, [
            'paginator' => new Paginator($query, $page),
            'lesson' => $lesson,
            'types' => array_flip(Question::getTypes())
        ]);
    }

    /**
     * @Route("/new/lesson/{uuid}", name="training_question_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @param Lesson $lesson
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, Lesson $lesson): Http\Response
    {
        $question = new Question();
        $question->setLesson($lesson);

        return $this->process($request, $question);
    }

    /**
     * @Route("/{uuid}/edit", name="training_question_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Question $question
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Question $question): Http\Response
    {
        return $this->process($request, $question);
    }

    /**
     * @Route("/{uuid}/copy", name="training_question_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Question $question
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Question $question): Http\Response
    {
        if ($request->get('deep')) {
            $question->cloneWithAssociations = true;
        }

        return $this->process($request, clone $question);
    }

    /**
     * @Route("/{uuid}", name="training_question_delete", methods="DELETE")
     *
     * @param Question $question
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Question $question, EntityService $entity): Http\JsonResponse
    {
        // Invalidate question answers
        $this->getDoctrine()
            ->getRepository(Answer::class)
            ->disableValidAnswers($question);

        return $entity->delete($question, $this->getResponseData($question));
    }

    /**
     * @param Question $question
     *
     * @return array
     */
    private function getResponseData(Question $question): array
    {
        return [
            'container' => '#training-questions-list',
            'url' => $this->generateUrl('training_question_filter', ['uuid' => $question->getLesson()->getUuid()])
        ];
    }

    /**
     * @param Http\Request $request
     * @param Question $question
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Question $question): Http\Response
    {
        $isNewElement = $question->getUuid() === null;

        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($question);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData($question), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('training/questions/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'question' => $question,
            'form' => $form->createView()
        ]);
    }
}
