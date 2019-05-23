<?php

namespace App\Controller\Accounts;

use App\Entity\Account\Account;
use App\Entity\Account\Code;
use App\Form\Account\CodeType;
use App\Tools\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/accounts")
 */
class CodesController extends AbstractController
{
    /**
     * @Route("/{uuid}/codes/{page<\d+>?1}", name="account_code_index", methods="GET")
     *
     * @param Account $account
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Account $account, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Code::class)
            ->search($account);

        return $this->render('accounts/codes/_index.html.twig', [
            'account' => $account,
            'paginator' => new Paginator($query, $page, 7)
        ]);
    }

    /**
     * @Route("/{uuid}/codes/new", name="account_code_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @param Account $account
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, Account $account): Http\Response
    {
        $code = new Code();
        $code->setAccount($account);

        return $this->process($request, $code);
    }

    /**
     * @param Account $account
     *
     * @return array
     */
    private function getResponseData(Account $account): array
    {
        return [
            'container' => '#account-codes',
            'url' => $this->generateUrl('account_code_index', ['uuid' => $account->getUuid()])
        ];
    }

    /**
     * @param Http\Request $request
     * @param Code $code
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Code $code): Http\Response
    {
        $form = $this->createForm(CodeType::class, $code);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($code);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData($code->getAccount()), 200);
            }

            return new Http\JsonResponse([], 206);
        }

        return $this->render('accounts/codes/new.html.twig', [
            'code' => $code,
            'form' => $form->createView(),
        ]);
    }
}
