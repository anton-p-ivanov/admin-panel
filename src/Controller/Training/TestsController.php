<?php

namespace App\Controller\Training;

use App\Entity\Training\Course;
use App\Entity\Training\Test;
use App\Form\Training\TestType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/training/tests")
 */
class TestsController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/course/{uuid}/{page<\d+>?1}", name="training_test_filter", methods="GET")
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
            ->getRepository(Test::class)
            ->search($course);

        $view = 'training/tests/index.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'training/tests/_index.html.twig';
        }

        return $this->render($view, [
            'paginator' => new Paginator($query, $page),
            'course' => $course
        ]);
    }

    /**
     * @Route("/new/course/{uuid}", name="training_test_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @param Course $course
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, Course $course): Http\Response
    {
        $test = new Test();
        $test->setCourse($course);

        return $this->process($request, $test);
    }

    /**
     * @Route("/{uuid}/edit", name="training_test_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Test $test
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Test $test): Http\Response
    {
        return $this->process($request, $test);
    }

    /**
     * @Route("/{uuid}/copy", name="training_test_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Test $test
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Test $test): Http\Response
    {
        if ($request->get('deep')) {
            $test->cloneWithAssociations = true;
        }

        return $this->process($request, clone $test);
    }

    /**
     * @Route("/{uuid}", name="training_test_delete", methods="DELETE")
     *
     * @param Test $test
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Test $test, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($test, $this->getResponseData($test));
    }

    /**
     * @param Test $test
     *
     * @return array
     */
    private function getResponseData(Test $test): array
    {
        return [
            'container' => '#training-tests-list',
            'url' => $this->generateUrl('training_test_filter', ['uuid' => $test->getCourse()->getUuid()])
        ];
    }

    /**
     * @param Http\Request $request
     * @param Test $test
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Test $test): Http\Response
    {
        $isNewElement = $test->getUuid() === null;

        $form = $this->createForm(TestType::class, $test);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($test);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData($test), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('training/tests/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'test' => $test,
            'form' => $form->createView(),
        ]);
    }
}
