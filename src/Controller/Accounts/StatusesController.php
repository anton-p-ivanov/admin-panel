<?php

namespace App\Controller\Accounts;

use App\Entity\Account\Account;
use App\Entity\Account\Status;
use App\Form\Account\StatusType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/accounts")
 */
class StatusesController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{uuid}/statuses/{page<\d+>?1}", name="account_status_index", methods="GET")
     *
     * @param Account $account
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Account $account, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Status::class)
            ->search(['account' => $account]);

        return $this->render('accounts/statuses/_index.html.twig', [
            'account' => $account,
            'paginator' => new Paginator($query, $page, 7)
        ]);
    }

    /**
     * @Route("/{uuid}/statuses/new", name="account_status_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @param Account $account
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, Account $account): Http\Response
    {
        $status = new Status();
        $status->setAccount($account);

        return $this->process($request, $status);
    }

    /**
     * @Route("/statuses/{uuid}/edit", name="account_status_edit", methods="GET|POST")
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
     * @Route("/statuses/{uuid}/copy", name="account_status_copy", methods="GET|POST")
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
     * @Route("/statuses/{uuid}", name="account_status_delete", methods="DELETE")
     *
     * @param Status $status
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Status $status, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($status, $this->getResponseData($status->getAccount()));
    }

    /**
     * @param Account $account
     *
     * @return array
     */
    private function getResponseData(Account $account): array
    {
        return [
            'url' => $this->generateUrl('account_status_index', ['uuid' => $account->getUuid()]),
            'container' => "#account-statuses"
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

                return new Http\JsonResponse($this->getResponseData($status->getAccount()), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('accounts/statuses/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'status' => $status,
            'form' => $form->createView(),
        ]);
    }
}
