<?php

namespace App\Controller\Users;

use App\Entity\User\User;
use App\Entity\User\Account;
use App\Form\User\AccountType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/users")
 */
class AccountsController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{uuid}/accounts/{page<\d+>?1}", name="user_account_index", methods="GET")
     *
     * @param User $user
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(User $user, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Account::class)
            ->search($user);

        return $this->render('users/accounts/_index.html.twig', [
            'user' => $user,
            'paginator' => new Paginator($query, $page)
        ]);
    }

    /**
     * @Route("/{uuid}/accounts/new", name="user_account_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @param User $user
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, User $user): Http\Response
    {
        $account = new Account();
        $account->setUser($user);

        return $this->process($request, $account);
    }

    /**
     * @Route("/accounts/{uuid}/edit", name="user_account_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Account $account
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Account $account): Http\Response
    {
        return $this->process($request, $account);
    }

    /**
     * @Route("/accounts/{uuid}/copy", name="user_account_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Account $account
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Account $account): Http\Response
    {
        return $this->process($request, clone $account);
    }

    /**
     * @Route("/accounts/{uuid}", name="user_account_delete", methods="DELETE")
     *
     * @param Account $account
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Account $account, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($account, $this->getResponseData($account->getUser()));
    }

    /**
     * @param User $user
     *
     * @return array
     */
    private function getResponseData(User $user): array
    {
        return [
            'url' => $this->generateUrl('user_account_index', ['uuid' => $user->getUuid()]),
            'container' => "#user-accounts"
        ];
    }

    /**
     * @param Http\Request $request
     * @param Account $account
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Account $account): Http\Response
    {
        $isNewElement = $account->getUuid() === null;

        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($account);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData($account->getUser()), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('users/accounts/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'userAccount' => $account,
            'form' => $form->createView(),
        ]);
    }
}
