<?php

namespace App\Controller\Forms;

use App\Entity\Form\Form;
use App\Entity\Form\Status;
use App\Form\Form\StatusType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/forms")
 */
class StatusesController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{uuid}/statuses/{page<\d+>?1}", name="form_status_index", methods="GET")
     *
     * @param Form $form
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Form $form, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Status::class)
            ->search(['form' => $form]);

        return $this->render('forms/statuses/_index.html.twig', [
            'form' => $form,
            'paginator' => new Paginator($query, $page, 5)
        ]);
    }

    /**
     * @Route("/{uuid}/statuses/new", name="form_status_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @param Form $form
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, Form $form): Http\Response
    {
        $status = new Status();
        $status->setForm($form);

        return $this->process($request, $status);
    }

    /**
     * @Route("/statuses/{uuid}/edit", name="form_status_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Status $status
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Status $status): Http\Response
    {
        return $this->process($request, $status);
    }

    /**
     * @Route("/statuses/{uuid}/copy", name="form_status_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Status $status
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Status $status): Http\Response
    {
        return $this->process($request, clone $status);
    }

    /**
     * @Route("/statuses/{uuid}", name="form_status_delete", methods="DELETE")
     *
     * @param Status $status
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Status $status, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($status, $this->getResponseData($status->getForm()));
    }

    /**
     * @param Form $form
     *
     * @return array
     */
    private function getResponseData(Form $form): array
    {
        return [
            'url' => $this->generateUrl('form_status_index', ['uuid' => $form->getUuid()]),
            'container' => "#form-statuses"
        ];
    }

    /**
     * @param Http\Request $request
     * @param Status $status
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Status $status): Http\Response
    {
        $isNewElement = $status->getUuid() === null;

        $form = $this->createForm(StatusType::class, $status);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($status);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData($status->getForm()), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('forms/statuses/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'status' => $status,
            'form' => $form->createView(),
        ]);
    }
}
