<?php

namespace App\Controller\Admin\Catalogs;

use App\Entity\Catalog\Type;
use App\Form\Catalog\TypeType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/catalogs/types")
 */
class TypesController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{page<\d+>?1}", name="admin_catalog_type_index", methods="GET")
     *
     * @param Http\Request $request
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Http\Request $request, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Type::class)
            ->search();

        $view = 'admin/catalogs/types/index.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'admin/catalogs/types/_index.html.twig';
        }

        return $this->render($view, [
            'paginator' => new Paginator($query, $page)
        ]);
    }

    /**
     * @Route("/new", name="admin_catalog_type_new", methods="GET|POST")
     *
     * @param Http\Request $request
     *
     * @return Http\Response
     */
    public function new(Http\Request $request): Http\Response
    {
        return $this->process($request, new Type());
    }

    /**
     * @Route("/{uuid}/edit", name="admin_catalog_type_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Type $type
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Type $type): Http\Response
    {
        return $this->process($request, $type);
    }

    /**
     * @Route("/{uuid}/copy", name="admin_catalog_type_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Type $type
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Type $type): Http\Response
    {
        return $this->process($request, clone $type);
    }

    /**
     * @Route("/{uuid}", name="admin_catalog_type_delete", methods="DELETE")
     *
     * @param Type $type
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Type $type, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($type, $this->getResponseData());
    }

    /**
     * @return array
     */
    private function getResponseData(): array
    {
        return [
            'url' => $this->generateUrl('admin_catalog_type_index'),
            'container' => '#catalog-types'
        ];
    }

    /**
     * @param Http\Request $request
     * @param Type $type
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Type $type): Http\Response
    {
        $isNewElement = $type->getUuid() === null;

        $form = $this->createForm(TypeType::class, $type);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($type);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData(), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('admin/catalogs/types/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'type' => $type,
            'form' => $form->createView(),
        ]);
    }
}
