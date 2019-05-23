<?php

namespace App\Controller\Training;

use App\Entity\Training\Answer;
use App\Entity\Training\Course;
use App\Entity\Training\Lesson;
use App\Form\Training\LessonType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/training/lessons")
 */
class LessonsController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/course/{uuid}/{page<\d+>?1}", name="training_lesson_filter", methods="GET")
     *
     * @param Http\Request $request
     * @param Course $course
     * @param int $page
     *
     * @return Http\Response
     */
    public function filter(Http\Request $request, Course $course, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Lesson::class)
            ->search($course);

        $view = 'training/lessons/index.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'training/lessons/_index.html.twig';
        }

        return $this->render($view, [
            'paginator' => new Paginator($query, $page),
            'course' => $course
        ]);
    }

    /**
     * @Route("/new/course/{uuid}", name="training_lesson_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @param Course $course
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, Course $course): Http\Response
    {
        $lesson = new Lesson();
        $lesson->setCourse($course);

        return $this->process($request, $lesson);
    }

    /**
     * @Route("/{uuid}/edit", name="training_lesson_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Lesson $lesson
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Lesson $lesson): Http\Response
    {
        return $this->process($request, $lesson);
    }

    /**
     * @Route("/{uuid}/copy", name="training_lesson_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Lesson $lesson
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Lesson $lesson): Http\Response
    {
        if ($request->get('deep')) {
            $lesson->cloneWithAssociations = true;
        }

        return $this->process($request, clone $lesson);
    }

    /**
     * @Route("/{uuid}", name="training_lesson_delete", methods="DELETE")
     *
     * @param Lesson $lesson
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Lesson $lesson, EntityService $entity): Http\JsonResponse
    {
        // Invalidate question answers
        foreach ($lesson->getQuestions() as $question) {
            $this->getDoctrine()
                ->getRepository(Answer::class)
                ->disableValidAnswers($question);
        }

        return $entity->delete($lesson, $this->getResponseData($lesson));
    }

    /**
     * @param Lesson $lesson
     *
     * @return array
     */
    private function getResponseData(Lesson $lesson): array
    {
        return [
            'container' => '#training-lessons-list',
            'url' => $this->generateUrl('training_lesson_filter', ['uuid' => $lesson->getCourse()->getUuid()])
        ];
    }

    /**
     * @param Http\Request $request
     * @param Lesson $lesson
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Lesson $lesson): Http\Response
    {
        $isNewElement = $lesson->getUuid() === null;

        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($lesson);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData($lesson), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('training/lessons/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'lesson' => $lesson,
            'form' => $form->createView(),
        ]);
    }
}
