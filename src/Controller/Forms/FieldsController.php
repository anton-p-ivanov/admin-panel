<?php

namespace App\Controller\Forms;

use App\Entity\Field\Field;
use App\Entity\Form\Form;
use App\Form\Field\FieldType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/forms")
 */
class FieldsController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{uuid}/fields/{page<\d+>?1}", name="form_field_index", methods="GET")
     *
     * @param Form $form
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Form $form, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Field::class)
            ->search($form->getHash());

        return $this->render('forms/fields/_index.html.twig', [
            'form' => $form,
            'paginator' => new Paginator($query, $page, 5)
        ]);
    }

    /**
     * @Route("/{uuid}/fields/new", name="form_field_new", methods="GET|POST")
     *
     * @param Form $form
     *
     * @return Http\Response
     */
    public function new(Form $form): Http\Response
    {
        $field = new Field();
        $field->setLabel('New field');
        $field->setHash($form->getHash());

        $form->addField($field);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($field);
        $manager->persist($form);
        $manager->flush();

        return $this->redirectToRoute('form_field_edit', ['uuid' => $field->getUuid()]);
    }

    /**
     * @Route("/fields/{uuid}/edit", name="form_field_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Field $field
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Field $field): Http\Response
    {
        return $this->process($request, $field, $this->getForm($field));
    }

    /**
     * @Route("/fields/{uuid}/copy", name="form_field_copy", methods="GET|POST")
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

        $form = $this->getForm($field);

        $clone = clone $field;
        $form->addField($clone);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($clone);
        $manager->persist($form);
        $manager->flush();

        return $this->redirectToRoute('form_field_edit', ['uuid' => $clone->getUuid()]);
    }

    /**
     * @param Field $field
     *
     * @return Form|null
     */
    private function getForm(Field $field)
    {
        $form = $this->getDoctrine()
            ->getRepository(Form::class)
            ->findByField($field);

        if (!$form) {
            throw new NotFoundHttpException();
        }

        return $form;
    }

    /**
     * @Route("/fields/{uuid}", name="form_field_delete", methods="DELETE")
     *
     * @param Field $field
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Field $field, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($field, $this->getResponseData($this->getForm($field)));
    }

    /**
     * @param Form $form
     *
     * @return array
     */
    private function getResponseData(Form $form): array
    {
        return [
            'url' => $this->generateUrl('form_field_index', ['uuid' => $form->getUuid()]),
            'container' => "#form-fields"
        ];
    }

    /**
     * @param Http\Request $request
     * @param Field $field
     * @param Form $entity
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Field $field, Form $entity): Http\Response
    {
        $form = $this->createForm(FieldType::class, $field);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($field);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData($entity), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('forms/fields/edit.html.twig', [
            'field' => $field,
            'form' => $form->createView()
        ]);
    }
}
