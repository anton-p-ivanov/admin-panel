<?php

namespace App\Controller\Training;

use App\Entity\Training\Attempt;
use App\Entity\Training\Test;
use App\Service\EntityService;
use App\Tools\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/training/attempts")
 */
class AttemptsController extends AbstractController
{
    /**
     * @Route("/test/{uuid}/{page<\d+>?1}", name="training_attempt_filter", methods="GET")
     *
     * @param Http\Request $request
     * @param Test $test
     * @param int $page
     *
     * @return Http\Response
     */
    public function filter(Http\Request $request, Test $test, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Attempt::class)
            ->search([
                'test' => $test
            ]);

        $view = 'training/attempts/index.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'training/attempts/_index.html.twig';
        }

        return $this->render($view, [
            'paginator' => new Paginator($query, $page),
            'test' => $test
        ]);
    }

    /**
     * @Route("/{uuid}", name="training_attempt_view", methods="GET")
     *
     * @param Attempt $attempt
     *
     * @return Http\Response
     */
    public function view(Attempt $attempt): Http\Response
    {
        $normalizedData = [];

        foreach ($attempt->getData() as $data) {
            if (!array_key_exists($data->getQuestionUuid(), $normalizedData)) {
                $normalizedData[$data->getQuestionUuid()] = [];
            }

            $normalizedData[$data->getQuestionUuid()][] = [
                'answer' => $data->getAnswer(),
                'isValid' => $data->getIsValid()
            ];
        }

        return $this->render('training/attempts/view.html.twig', [
            'attempt' => $attempt,
            'data' => $normalizedData
        ]);
    }

    /**
     * @Route("/{uuid}", name="training_attempt_delete", methods="DELETE")
     *
     * @param Attempt $attempt
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Attempt $attempt, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($attempt, [
            'container' => '#training-attempts-list',
            'url' => $this->generateUrl('training_attempt_filter', [
                'uuid' => $attempt->getTest()->getUuid()
            ])
        ]);
    }
}
