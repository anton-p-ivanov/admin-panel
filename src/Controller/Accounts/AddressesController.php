<?php

namespace App\Controller\Accounts;

use App\Entity\Account\Account;
use App\Entity\Address;
use App\Form\AddressType;
use App\Service\EntityService;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/accounts")
 */
class AddressesController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{uuid}/addresses", name="account_address_index", methods="GET")
     *
     * @param Account $account
     *
     * @return Http\Response
     */
    public function index(Account $account): Http\Response
    {
        return $this->render('accounts/addresses/_index.html.twig', ['account' => $account]);
    }

    /**
     * @Route("/{uuid}/addresses/new", name="account_address_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @param Account $account
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, Account $account): Http\Response
    {
        $address = new Address();

        $doctrine = $this->getDoctrine();
        $properties = [
            'country' => $doctrine->getRepository('App:Country')->findOneBy(['code' => 'RU']),
            'type' => $doctrine->getRepository('App:AddressType')->findOneBy(['isDefault' => true])
        ];

        foreach ($properties as $property => $value) {
            $address->{'set' . ucfirst($property)}($value);
        }

        $account->addAddress($address);

        return $this->process($request, $address, $account);
    }

    /**
     * @Route("/{account}/addresses/{uuid}/edit", name="account_address_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Address $address
     * @param Account $account
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Account $account, Address $address): Http\Response
    {
        return $this->process($request, $address, $account);
    }

    /**
     * @Route("/{account}/addresses/{uuid}/copy", name="account_address_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Account $account
     * @param Address $address
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Address $address, Account $account): Http\Response
    {
        $clone = clone $address;
        $account->addAddress($clone);

        return $this->process($request, $clone, $account);
    }

    /**
     * @Route("/{account}/addresses/{uuid}", name="account_address_delete", methods="DELETE")
     *
     * @param Account $account
     * @param Address $address
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Account $account, Address $address, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($address, $this->getResponseData($account));
    }

    /**
     * @param Account $account
     *
     * @return array
     */
    private function getResponseData(Account $account): array
    {
        return [
            'url' => $this->generateUrl('account_address_index', ['uuid' => $account->getUuid()]),
            'container' => "#account-addresses"
        ];
    }

    /**
     * @param Http\Request $request
     * @param Address $address
     * @param Account $account
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Address $address, Account $account): Http\Response
    {
        $isNewElement = $address->getUuid() === null;

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($address);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData($account), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('accounts/addresses/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'address' => $address,
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }
}
