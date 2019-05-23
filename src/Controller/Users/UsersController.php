<?php

namespace App\Controller\Users;

use App\Entity\User\User;
use App\Form\User\UserType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/users")
 */
class UsersController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{page<\d+>?1}", name="user_index", methods="GET")
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
            ->getRepository(User::class)
            ->search($search);

        if ($view) {
            $viewFile = "users/users/$view.html.twig";
        }
        else {
            $viewFile = "users/users/index.html.twig";
            if ($request->isXmlHttpRequest()) {
                $viewFile = "users/users/_index.html.twig";
            }
        }

        return $this->render($viewFile, [
            'paginator' => new Paginator($query, $page, $view ? 10 : null)
        ]);
    }

    /**
     * @Route("/new", name="user_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @return Http\Response
     */
    public function new(Http\Request $request): Http\Response
    {
        return $this->process($request, new User());
    }

    /**
     * @Route("/{uuid}/edit", name="user_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param User $user
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, User $user): Http\Response
    {
        return $this->process($request, $user);
    }

    /**
     * @Route("/{uuid}/copy", name="user_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param User $user
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, User $user): Http\Response
    {
        return $this->process($request, clone $user);
    }

    /**
     * @Route("/{uuid}", name="user_delete", methods="DELETE")
     *
     * @param User $user
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(User $user, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($user, $this->getResponseData());
    }

    /**
     * @return array
     */
    private function getResponseData(): array
    {
        return [
            'url' => $this->generateUrl('user_index'),
            'container' => '#users-list'
        ];
    }

    /**
     * @param Http\Request $request
     * @param User $user
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, User $user): Http\Response
    {
        $isNewElement = $user->getUuid() === null;

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($user);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData(), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('users/users/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
