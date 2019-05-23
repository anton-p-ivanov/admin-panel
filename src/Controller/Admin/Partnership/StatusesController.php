<?php

namespace App\Controller\Admin\Partnership;

use App\Entity\PartnershipStatus;
use App\Form\PartnershipStatusType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/partnership/statuses")
 */
class StatusesController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{page<\d+>?1}", name="partnership_status_index", methods="GET")
     *
     * @param Http\Request $request
     * @param int|null $page
     *
     * @return Http\Response
     */
    public function index(Http\Request $request, ?int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(PartnershipStatus::class)
            ->search();

        $view = 'admin/partnership/statuses/index.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'admin/partnership/statuses/_index.html.twig';
        }

        return $this->render($view, [
            'paginator' => new Paginator($query, $page)
        ]);
    }

    /**
     * @Route("/new", name="partnership_status_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @return Http\Response
     */
    public function new(Http\Request $request): Http\Response
    {
        return $this->process($request, new PartnershipStatus());
    }

    /**
     * @Route("/{uuid}/edit", name="partnership_status_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param PartnershipStatus $status
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, PartnershipStatus $status): Http\Response
    {
        return $this->process($request, $status);
    }

    /**
     * @Route("/{uuid}/copy", name="partnership_status_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param PartnershipStatus $status
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, PartnershipStatus $status): Http\Response
    {
        return $this->process($request, clone $status);
    }

    /**
     * @Route("/{uuid}", name="partnership_status_delete", methods="DELETE")
     *
     * @param PartnershipStatus $status
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(PartnershipStatus $status, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($status, $this->getResponseData());
    }

    /**
     * @return array
     */
    private function getResponseData(): array
    {
        return [
            'url' => $this->generateUrl('partnership_status_index'),
            'container' => '#admin-partnership-statuses-list'
        ];
    }

    /**
     * @param Http\Request $request
     * @param PartnershipStatus $status
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, PartnershipStatus $status): Http\Response
    {
        $isNewElement = $status->getUuid() === null;

        $form = $this->createForm(PartnershipStatusType::class, $status);
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

        return $this->render('admin/partnership/statuses/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'status' => $status,
            'form' => $form->createView(),
        ]);
    }
}
