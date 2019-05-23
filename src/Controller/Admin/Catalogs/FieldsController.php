<?php

namespace App\Controller\Admin\Catalogs;

use App\Entity\Catalog\Catalog;
use App\Entity\Field\Field;
use App\Form\Field\FieldType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/catalogs")
 */
class FieldsController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{uuid}/fields/{page<\d+>?1}", name="catalog_field_index", methods="GET")
     *
     * @param Catalog $catalog
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Catalog $catalog, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Field::class)
            ->search($catalog->getHash());

        return $this->render('admin/catalogs/fields/_index.html.twig', [
            'catalog' => $catalog,
            'paginator' => new Paginator($query, $page, 5)
        ]);
    }

    /**
     * @Route("/{uuid}/fields/new", name="catalog_field_new", methods="GET|POST")
     *
     * @param Catalog $catalog
     *
     * @return Http\Response
     */
    public function new(Catalog $catalog): Http\Response
    {
        $field = new Field();
        $field->setLabel('New field');
        $field->setHash($catalog->getHash());

        $catalog->addField($field);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($field);
        $manager->persist($catalog);
        $manager->flush();

        return $this->redirectToRoute('catalog_field_edit', ['uuid' => $field->getUuid()]);
    }

    /**
     * @Route("/fields/{uuid}/edit", name="catalog_field_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Field $field
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Field $field): Http\Response
    {
        return $this->process($request, $field, $this->getCatalog($field));
    }

    /**
     * @Route("/fields/{uuid}/copy", name="catalog_field_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Field $field
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Field $field): Http\Response
    {
        if ($request->get('deep')) {
            $field->cloneWithAssociations = true;
        }

        $catalog = $this->getCatalog($field);

        $clone = clone $field;
        $catalog->addField($clone);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($clone);
        $manager->persist($catalog);
        $manager->flush();

        return $this->redirectToRoute('catalog_field_edit', ['uuid' => $clone->getUuid()]);
    }

    /**
     * @param Field $field
     *
     * @return Catalog|null
     */
    private function getCatalog(Field $field)
    {
        $form = $this->getDoctrine()
            ->getRepository(Catalog::class)
            ->findByField($field);

        if (!$form) {
            throw new NotFoundHttpException();
        }

        return $form;
    }

    /**
     * @Route("/fields/{uuid}", name="catalog_field_delete", methods="DELETE")
     *
     * @param Field $field
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Field $field, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($field, $this->getResponseData($this->getCatalog($field)));
    }

    /**
     * @param Catalog $catalog
     *
     * @return array
     */
    private function getResponseData(Catalog $catalog): array
    {
        return [
            'url' => $this->generateUrl('catalog_field_index', ['uuid' => $catalog->getUuid()]),
            'container' => "#form-fields"
        ];
    }

    /**
     * @param Http\Request $request
     * @param Field $field
     * @param Catalog $catalog
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Field $field, Catalog $catalog): Http\Response
    {
        $form = $this->createForm(FieldType::class, $field);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($field);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData($catalog), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('admin/catalogs/fields/edit.html.twig', [
            'field' => $field,
            'form' => $form->createView()
        ]);
    }
}
