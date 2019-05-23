<?php

namespace App\Controller\Training;

use App\Entity\Training\Answer;
use App\Entity\Training\Question;
use App\Form\Training\AnswerType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/training/answers")
 */
class AnswersController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/question/{uuid}/{page<\d+>?1}", name="training_answer_filter", methods="GET")
     *
     * @param Http\Request $request
     * @param Question $question
     * @param int $page
     *
     * @return Http\Response
     */
    public function filter(Http\Request $request, Question $question, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Answer::class)
            ->search($question);

        $view = 'training/answers/index.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'training/answers/_index.html.twig';
        }

        return $this->render($view, [
            'paginator' => new Paginator($query, $page),
            'question' => $question
        ]);
    }

    /**
     * @Route("/new/question/{uuid}", name="training_answer_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @param Question $question
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, Question $question): Http\Response
    {
        $answer = new Answer();
        $answer->setQuestion($question);

        return $this->process($request, $answer);
    }

    /**
     * @Route("/{uuid}/edit", name="training_answer_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Answer $answer
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Answer $answer): Http\Response
    {
        return $this->process($request, $answer);
    }

    /**
     * @Route("/{uuid}/copy", name="training_answer_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Answer $answer
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Answer $answer): Http\Response
    {
        return $this->process($request, clone $answer);
    }

    /**
     * @Route("/{uuid}", name="training_answer_delete", methods="DELETE")
     *
     * @param Answer $answer
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Answer $answer, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($answer, $this->getResponseData($answer));
    }

    /**
     * @param Answer $answer
     *
     * @return array
     */
    private function getResponseData(Answer $answer): array
    {
        return [
            'container' => '#training-answers-list',
            'url' => $this->generateUrl('training_answer_filter', ['uuid' => $answer->getQuestion()->getUuid()]),
        ];
    }

    /**
     * @param Http\Request $request
     * @param Answer $answer
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Answer $answer): Http\Response
    {
        $isNewElement = $answer->getUuid() === null;

        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($answer);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData($answer), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('training/answers/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'answer' => $answer,
            'form' => $form->createView(),
        ]);
    }
}
