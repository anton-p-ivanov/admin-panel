<?php

namespace App\Controller\Admin\Partnership;

use App\Entity\PartnershipStatus;
use App\Entity\PartnershipDiscount;
use App\Form\PartnershipDiscountType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/partnership/statuses")
 */
class DiscountsController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{uuid}/discounts/{page<\d+>?1}", name="partnership_discount_index", methods="GET")
     *
     * @param PartnershipStatus $status
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(PartnershipStatus $status, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(PartnershipDiscount::class)
            ->search(['status' => $status]);

        return $this->render('admin/partnership/discounts/_index.html.twig', [
            'status' => $status,
            'paginator' => new Paginator($query, $page)
        ]);
    }

    /**
     * @Route("/{uuid}/discounts/new", name="partnership_discount_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @param PartnershipStatus $status
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, PartnershipStatus $status): Http\Response
    {
        $discount = new PartnershipDiscount();
        $discount->setStatus($status);

        return $this->process($request, $discount);
    }

    /**
     * @Route("/discounts/{uuid}/edit", name="partnership_discount_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param PartnershipDiscount $discount
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, PartnershipDiscount $discount): Http\Response
    {
        return $this->process($request, $discount);
    }

    /**
     * @Route("/managers/{uuid}/copy", name="partnership_discount_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param PartnershipDiscount $discount
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, PartnershipDiscount $discount): Http\Response
    {
        return $this->process($request, clone $discount);
    }

    /**
     * @Route("/managers/{uuid}", name="partnership_discount_delete", methods="DELETE")
     *
     * @param PartnershipDiscount $discount
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(PartnershipDiscount $discount, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($discount, $this->getResponseData($discount->getStatus()));
    }

    /**
     * @param PartnershipStatus $status
     *
     * @return array
     */
    private function getResponseData(PartnershipStatus $status): array
    {
        return [
            'url' => $this->generateUrl('partnership_discount_index', ['uuid' => $status->getUuid()]),
            'container' => "#status-discounts"
        ];
    }

    /**
     * @param Http\Request $request
     * @param PartnershipDiscount $discount
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, PartnershipDiscount $discount): Http\Response
    {
        $isNewElement = $discount->getUuid() === null;

        $form = $this->createForm(PartnershipDiscountType::class, $discount);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($discount);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData($discount->getStatus()), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('admin/partnership/discounts/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'discount' => $discount,
            'form' => $form->createView(),
        ]);
    }
}
