<?php

namespace App\Controller\Training;

use App\Entity\Training\Answer;
use App\Entity\Training\Course;
use App\Form\Training\CourseType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/training/courses")
 */
class CoursesController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{page<\d+>?1}", name="training_course_index", methods="GET")
     *
     * @param Http\Request $request
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Http\Request $request, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Course::class)
            ->search();

        $view = 'training/courses/index.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'training/courses/_index.html.twig';
        }

        return $this->render($view, ['paginator' => new Paginator($query, $page)]);
    }

    /**
     * @Route("/new", name="training_course_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @return Http\Response
     */
    public function new(Http\Request $request): Http\Response
    {
        return $this->process($request, new Course());
    }

    /**
     * @Route("/{uuid}/edit", name="training_course_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Course $course
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Course $course): Http\Response
    {
        return $this->process($request, $course);
    }

    /**
     * @Route("/{uuid}/copy", name="training_course_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Course $course
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Course $course): Http\Response
    {
        if ($request->get('deep')) {
            $course->cloneWithAssociations = true;
        }

        return $this->process($request, clone $course);
    }

    /**
     * @Route("/{uuid}", name="training_course_delete", methods="DELETE")
     *
     * @param Course $course
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Course $course, EntityService $entity): Http\JsonResponse
    {
        // Invalidate question answers
        foreach ($course->getLessons() as $lesson) {
            foreach ($lesson->getQuestions() as $question) {
                $this->getDoctrine()
                    ->getRepository(Answer::class)
                    ->disableValidAnswers($question);
            }
        }

        return $entity->delete($course, $this->getResponseData());
    }

    /**
     * @return array
     */
    private function getResponseData(): array
    {
        return [
            'container' => '#training-courses-list',
            'url' => $this->generateUrl('training_course_index')
        ];
    }

    /**
     * @param Http\Request $request
     * @param Course $course
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Course $course): Http\Response
    {
        $isNewElement = $course->getUuid() === null;

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($course);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData(), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('training/courses/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'course' => $course,
            'form' => $form->createView(),
        ]);
    }
}
