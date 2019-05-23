<?php

namespace App\Controller\Admin;

use App\Entity\Role;
use App\Form\RoleType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/roles")
 */
class RolesController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{page<\d+>?1}", name="role_index", methods="GET")
     *
     * @param Http\Request $request
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Http\Request $request, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Role::class)
            ->search();

        $view = 'admin/roles/index.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'admin/roles/_index.html.twig';
        }

        return $this->render($view, ['paginator' => new Paginator($query, $page)]);
    }

    /**
     * @Route("/new", name="role_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @return Http\Response
     */
    public function new(Http\Request $request): Http\Response
    {
        return $this->process($request, new Role());
    }

    /**
     * @Route("/{uuid}/edit", name="role_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Role $role
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Role $role): Http\Response
    {
        return $this->process($request, $role);
    }

    /**
     * @Route("/{uuid}/copy", name="role_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Role $role
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Role $role): Http\Response
    {
        return $this->process($request, clone $role);
    }

    /**
     * @Route("/{uuid}", name="role_delete", methods="DELETE")
     *
     * @param Role $role
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Role $role, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($role, $this->getResponseData());
    }

    /**
     * @return array
     */
    private function getResponseData(): array
    {
        return [
            'url' => $this->generateUrl('role_index'),
            'container' => '#admin-roles'
        ];
    }

    /**
     * @param Http\Request $request
     * @param Role $role
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Role $role): Http\Response
    {
        $isNewElement = $role->getUuid() === null;

        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($role);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData(), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('admin/roles/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'role' => $role,
            'form' => $form->createView(),
        ]);
    }
}
