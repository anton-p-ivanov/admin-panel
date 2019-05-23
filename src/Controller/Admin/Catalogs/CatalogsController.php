<?php

namespace App\Controller\Admin\Catalogs;

use App\Entity\Catalog\Catalog;
use App\Entity\Catalog\Type;
use App\Form\Catalog\CatalogType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/catalogs")
 */
class CatalogsController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{uuid<[\w\-]{36}>}/{page<\d+>?1}", name="admin_catalog_index", methods="GET")
     *
     * @param Http\Request $request
     * @param Type $type
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Http\Request $request, Type $type, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Catalog::class)
            ->search(['type' => $type]);

        $view = 'admin/catalogs/catalogs/index.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'admin/catalogs/catalogs/_index.html.twig';
        }

        return $this->render($view, [
            'type' => $type,
            'paginator' => new Paginator($query, $page)
        ]);
    }

    /**
     * @Route("/{uuid<[\w\-]{36}>}/new", name="admin_catalog_new", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Type $type
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, Type $type): Http\Response
    {
        $catalog = new Catalog();
        $catalog->setType($type);

        return $this->process($request, $catalog);
    }

    /**
     * @Route("/{uuid}/edit", name="admin_catalog_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Catalog $catalog
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Catalog $catalog): Http\Response
    {
        return $this->process($request, $catalog);
    }

    /**
     * @Route("/{uuid}/copy", name="admin_catalog_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Catalog $catalog
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Catalog $catalog): Http\Response
    {
        if ($request->get('deep')) {
            $catalog->cloneWithAssociations = true;
        }

        $clone = clone $catalog;

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($clone);
        $manager->flush();

        if ($catalog->cloneWithAssociations) {
            $this->copyFields($catalog, $clone);
        }

        return $this->redirectToRoute('admin_catalog_edit', ['uuid' => $clone->getUuid()]);
    }

    /**
     * @param Catalog $original
     * @param Catalog $clone
     */
    private function copyFields(Catalog $original, Catalog $clone)
    {
        foreach ($original->getFields() as $field) {
            $field->cloneWithAssociations = $original->cloneWithAssociations;
            $field = clone $field;
            $field->setHash($clone->getHash());
            $clone->getFields()->add($field);
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($clone);
        $manager->flush();
    }

    /**
     * @Route("/{uuid}", name="admin_catalog_delete", methods="DELETE")
     *
     * @param Catalog $catalog
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Catalog $catalog, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($catalog, $this->getResponseData($catalog));
    }

    /**
     * @param Catalog $catalog
     *
     * @return array
     */
    private function getResponseData(Catalog $catalog): array
    {
        return [
            'url' => $this->generateUrl('admin_catalog_index', ['uuid' => $catalog->getType()->getUuid()]),
            'container' => '#catalogs-list'
        ];
    }

    /**
     * @param Http\Request $request
     * @param Catalog $catalog
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Catalog $catalog): Http\Response
    {
        $isNewElement = $catalog->getUuid() === null;

        /* @var $form Form */
        $form = $this->createForm(CatalogType::class, $catalog);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($catalog);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData($catalog), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('admin/catalogs/catalogs/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'catalog' => $catalog,
            'form' => $form->createView(),
        ]);
    }
}
