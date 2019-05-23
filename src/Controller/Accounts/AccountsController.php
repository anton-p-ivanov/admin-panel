<?php

namespace App\Controller\Accounts;

use App\Entity\Account\Account;
use App\Entity\Account\Code;
use App\Entity\Workflow;
use App\Entity\WorkflowStatus;
use App\Form\Account\AccountType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/accounts")
 */
class AccountsController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{page<\d+>?1}", name="account_index", methods="GET")
     *
     * @param Http\Request $request
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Http\Request $request, int $page): Http\Response
    {
        $view = $request->get('view');
        $search = $request->get('search');
        $query = $this->getDoctrine()
            ->getRepository(Account::class)
            ->search($search);

        if ($view) {
            $viewFile = "accounts/accounts/$view.html.twig";
        }
        else {
            $viewFile = "accounts/accounts/index.html.twig";
            if ($request->isXmlHttpRequest()) {
                $viewFile = "accounts/accounts/_index.html.twig";
            }
        }

        return $this->render($viewFile, [
            'paginator' => new Paginator($query, $page, $view ? 10 : null)
        ]);
    }

    /**
     * @Route("/new", name="account_new", methods="GET|POST")
     *
     * @param Http\Request $request
     *
     * @return Http\Response
     */
    public function new(Http\Request $request): Http\Response
    {
        $account = new Account();
        $attributes = [
            'workflow' => $this->getWorkflow()
        ];

        foreach ($attributes as $attribute => $value) {
            $account->{'set' . ucfirst($attribute)}($value);
        }

        return $this->process($request, $account);
    }

    /**
     * @Route("/{uuid}/edit", name="account_edit", methods="GET|POST")
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
     * @Route("/{uuid}/copy", name="account_copy", methods="GET|POST")
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
     * @Route("/{uuid}", name="account_delete", methods="DELETE")
     *
     * @param Account $account
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Account $account, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($account, $this->getResponseData());
    }

    /**
     * @return array
     */
    private function getResponseData()
    {
        return [
            'url' => $this->generateUrl('account_index'),
            'container' => '#accounts-list'
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
            if ($account->updateAccountCode) {
                $account->addAccountCode(new Code());
            }

            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($account);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData(), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('accounts/accounts/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return Workflow
     */
    private function getWorkflow(): Workflow
    {
        /* @var $status WorkflowStatus */
        $status = $this->getDoctrine()
            ->getRepository(WorkflowStatus::class)
            ->findOneBy(['code' => 'PUBLISHED']);

        $workflow = new Workflow();
        $workflow->setStatus($status);

        return $workflow;
    }
}
