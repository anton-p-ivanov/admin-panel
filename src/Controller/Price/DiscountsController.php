<?php

namespace App\Controller\Price;

use App\Entity\Price\Price;
use App\Entity\Price\Discount;
use App\Form\Price\DiscountType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/prices")
 */
class DiscountsController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{uuid}/discounts/{page<\d+>?1}", name="price_discount_index", methods="GET")
     *
     * @param Price $price
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Price $price, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Discount::class)
            ->search($price);

        return $this->render('prices/discounts/_index.html.twig', [
            'price' => $price,
            'paginator' => new Paginator($query, $page, 7)
        ]);
    }

    /**
     * @Route("/{uuid}/discounts/new", name="price_discount_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @param Price $price
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, Price $price): Http\Response
    {
        $discount = new Discount();
        $discount->setPrice($price);

        return $this->process($request, $discount);
    }

    /**
     * @Route("/discounts/{uuid}/edit", name="price_discount_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Discount $discount
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Discount $discount): Http\Response
    {
        return $this->process($request, $discount);
    }

    /**
     * @Route("/discounts/{uuid}/copy", name="price_discount_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Discount $discount
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Discount $discount): Http\Response
    {
        return $this->process($request, clone $discount);
    }

    /**
     * @Route("/discounts/{uuid}", name="price_discount_delete", methods="DELETE")
     *
     * @param Discount $discount
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Discount $discount, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($discount, $this->getResponseData($discount->getPrice()));
    }

    /**
     * @param Price $price
     *
     * @return array
     */
    private function getResponseData(Price $price): array
    {
        return [
            'url' => $this->generateUrl('price_discount_index', ['uuid' => $price->getUuid()]),
            'container' => "#form-discounts"
        ];
    }

    /**
     * @param Http\Request $request
     * @param Discount $discount
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Discount $discount): Http\Response
    {
        $isNewElement = $discount->getUuid() === null;

        $form = $this->createForm(DiscountType::class, $discount);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $objectManager = $this->getDoctrine()->getManager();
                $objectManager->persist($discount);
                $objectManager->flush();

                return new Http\JsonResponse($this->getResponseData($discount->getPrice()), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('prices/discounts/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'discount' => $discount,
            'form' => $form->createView(),
        ]);
    }
}
