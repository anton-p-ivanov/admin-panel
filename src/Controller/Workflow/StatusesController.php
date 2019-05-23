<?php

namespace App\Controller\Workflow;

use App\Entity\WorkflowStatus;
use App\Form\WorkflowStatusType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/workflow/statuses")
 */
class StatusesController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{page<\d+>?1}", name="workflow_status_index", methods="GET")
     *
     * @param Http\Request $request
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Http\Request $request, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(WorkflowStatus::class)
            ->search();

        $view = 'workflow/statuses/index.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'workflow/statuses/_index.html.twig';
        }

        return $this->render($view, ['paginator' => new Paginator($query, $page)]);
    }

    /**
     * @Route("/new", name="workflow_status_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @return Http\Response
     */
    public function new(Http\Request $request): Http\Response
    {
        return $this->process($request, new WorkflowStatus());
    }

    /**
     * @Route("/{uuid}/edit", name="workflow_status_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param WorkflowStatus $status
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, WorkflowStatus $status): Http\Response
    {
        return $this->process($request, $status);
    }

    /**
     * @Route("/{uuid}/copy", name="workflow_status_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param WorkflowStatus $status
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, WorkflowStatus $status): Http\Response
    {
        return $this->process($request, clone $status);
    }

    /**
     * @Route("/{uuid}", name="workflow_status_delete", methods="DELETE")
     *
     * @param WorkflowStatus $status
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(WorkflowStatus $status, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($status, $this->getResponseData());
    }

    /**
     * @return array
     */
    private function getResponseData(): array
    {
        return [
            'url' => $this->generateUrl('workflow_status_index'),
            'container' => '#workflow-statuses-list'
        ];
    }

    /**
     * @param Http\Request $request
     * @param WorkflowStatus $status
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, WorkflowStatus $status): Http\Response
    {
        $isNewElement = $status->getUuid() === null;

        $form = $this->createForm(WorkflowStatusType::class, $status);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($status);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData(), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('workflow/statuses/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'status' => $status,
            'form' => $form->createView(),
        ]);
    }
}
