<?php

namespace App\Controller\Admin\Sales;

use App\Entity\SalesDiscount;
use App\Form\SalesDiscountType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/sales/discounts")
 */
class DiscountsController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{page<\d+>?1}", name="sales_discount_index", methods="GET")
     *
     * @param Http\Request $request
     * @param int|null $page
     *
     * @return Http\Response
     */
    public function index(Http\Request $request, ?int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(SalesDiscount::class)
            ->search();

        $view = 'admin/sales/discounts/index.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'admin/sales/discounts/_index.html.twig';
        }

        return $this->render($view, [
            'paginator' => new Paginator($query, $page)
        ]);
    }

    /**
     * @Route("/new", name="sales_discount_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @return Http\Response
     */
    public function new(Http\Request $request): Http\Response
    {
        return $this->process($request, new SalesDiscount());
    }

    /**
     * @Route("/{uuid}/edit", name="sales_discount_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param SalesDiscount $discount
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, SalesDiscount $discount): Http\Response
    {
        return $this->process($request, $discount);
    }

    /**
     * @Route("/{uuid}/copy", name="sales_discount_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param SalesDiscount $discount
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, SalesDiscount $discount): Http\Response
    {
        return $this->process($request, clone $discount);
    }

    /**
     * @Route("/{uuid}", name="sales_discount_delete", methods="DELETE")
     *
     * @param SalesDiscount $discount
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(SalesDiscount $discount, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($discount, $this->getResponseData());
    }

    /**
     * @return array
     */
    private function getResponseData(): array
    {
        return [
            'url' => $this->generateUrl('sales_discount_index'),
            'container' => '#sales-discounts'
        ];
    }

    /**
     * @param Http\Request $request
     * @param SalesDiscount $discount
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, SalesDiscount $discount): Http\Response
    {
        $isNewElement = $discount->getUuid() === null;

        $form = $this->createForm(SalesDiscountType::class, $discount);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($discount);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData(), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('admin/sales/discounts/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'discount' => $discount,
            'form' => $form->createView(),
        ]);
    }
}
