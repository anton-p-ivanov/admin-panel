<?php

namespace App\Controller\Accounts;

use App\Entity\Account\Account;
use App\Entity\Account\Discount;
use App\Form\Account\DiscountType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/accounts")
 */
class DiscountsController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{uuid}/discounts/{page<\d+>?1}", name="account_discount_index", methods="GET")
     *
     * @param Account $account
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Account $account, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Discount::class)
            ->search(['account' => $account]);

        return $this->render('accounts/discounts/_index.html.twig', [
            'account' => $account,
            'paginator' => new Paginator($query, $page, 7)
        ]);
    }

    /**
     * @Route("/{uuid}/discounts/new", name="account_discount_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @param Account $account
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, Account $account): Http\Response
    {
        $discount = new Discount();
        $discount->setAccount($account);

        return $this->process($request, $discount);
    }

    /**
     * @Route("/discounts/{uuid}/edit", name="account_discount_edit", methods="GET|POST")
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
     * @Route("/discounts/{uuid}/copy", name="account_discount_copy", methods="GET|POST")
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
     * @Route("/discounts/{uuid}", name="account_discount_delete", methods="DELETE")
     *
     * @param Discount $discount
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Discount $discount, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($discount, $this->getResponseData($discount->getAccount()));
    }

    /**
     * @param Account $account
     *
     * @return array
     */
    private function getResponseData(Account $account): array
    {
        return [
            'url' => $this->generateUrl('account_discount_index', ['uuid' => $account->getUuid()]),
            'container' => "#account-discounts"
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
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($discount);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData($discount->getAccount()), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('accounts/discounts/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'discount' => $discount,
            'form' => $form->createView(),
        ]);
    }
}
