<?php

namespace App\Controller\Accounts;

use App\Entity\Account\Account;
use App\Entity\Account\Manager;
use App\Form\Account\ManagerType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/accounts")
 */
class ManagersController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{uuid}/managers/{page<\d+>?1}", name="account_manager_index", methods="GET")
     *
     * @param Account $account
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Account $account, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Manager::class)
            ->search(['account' => $account]);

        return $this->render('accounts/managers/_index.html.twig', [
            'account' => $account,
            'paginator' => new Paginator($query, $page, 7)
        ]);
    }

    /**
     * @Route("/{uuid}/managers/new", name="account_manager_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @param Account $account
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, Account $account): Http\Response
    {
        $manager = new Manager();
        $manager->setAccount($account);

        return $this->process($request, $manager);
    }

    /**
     * @Route("/managers/{uuid}/edit", name="account_manager_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Manager $manager
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Manager $manager): Http\Response
    {
        return $this->process($request, $manager);
    }

    /**
     * @Route("/managers/{uuid}/copy", name="account_manager_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Manager $manager
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Manager $manager): Http\Response
    {
        return $this->process($request, clone $manager);
    }

    /**
     * @Route("/managers/{uuid}", name="account_manager_delete", methods="DELETE")
     *
     * @param Manager $manager
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Manager $manager, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($manager, $this->getResponseData($manager->getAccount()));
    }

    /**
     * @param Account $account
     *
     * @return array
     */
    private function getResponseData(Account $account): array
    {
        return [
            'url' => $this->generateUrl('account_manager_index', ['uuid' => $account->getUuid()]),
            'container' => "#account-managers"
        ];
    }

    /**
     * @param Http\Request $request
     * @param Manager $manager
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Manager $manager): Http\Response
    {
        $isNewElement = $manager->getUuid() === null;

        $form = $this->createForm(ManagerType::class, $manager);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $objectManager = $this->getDoctrine()->getManager();
                $objectManager->persist($manager);
                $objectManager->flush();

                return new Http\JsonResponse($this->getResponseData($manager->getAccount()), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('accounts/managers/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'manager' => $manager,
            'form' => $form->createView(),
        ]);
    }
}
