<?php

namespace App\Controller\Accounts;

use App\Entity\Account\Account;
use App\Entity\Account\Contact;
use App\Form\Account\ContactType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/accounts")
 */
class ContactsController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{uuid}/contacts/{page<\d+>?1}", name="account_contact_index", methods="GET")
     *
     * @param Account $account
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Account $account, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Contact::class)
            ->search(['account' => $account]);

        return $this->render('accounts/contacts/_index.html.twig', [
            'account' => $account,
            'paginator' => new Paginator($query, $page, 7)
        ]);
    }

    /**
     * @Route("/{uuid}/contacts/new", name="account_contact_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @param Account $account
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, Account $account): Http\Response
    {
        $contact = new Contact();
        $contact->setAccount($account);

        return $this->process($request, $contact);
    }

    /**
     * @Route("/contacts/{uuid}/edit", name="account_contact_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Contact $contact
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Contact $contact): Http\Response
    {
        return $this->process($request, $contact);
    }

    /**
     * @Route("/contacts/{uuid}/copy", name="account_contact_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Contact $contact
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Contact $contact): Http\Response
    {
        return $this->process($request, clone $contact);
    }

    /**
     * @Route("/contacts/{uuid}", name="account_contact_delete", methods="DELETE")
     *
     * @param Contact $contact
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Contact $contact, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($contact, $this->getResponseData($contact->getAccount()));
    }

    /**
     * @param Account $account
     *
     * @return array
     */
    private function getResponseData(Account $account): array
    {
        return [
            'url' => $this->generateUrl('account_contact_index', ['uuid' => $account->getUuid()]),
            'container' => "#account-contacts"
        ];
    }

    /**
     * @param Http\Request $request
     * @param Contact $contact
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Contact $contact): Http\Response
    {
        $isNewElement = $contact->getUuid() === null;

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($contact);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData($contact->getAccount()), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('accounts/contacts/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'contact' => $contact,
            'form' => $form->createView(),
        ]);
    }
}