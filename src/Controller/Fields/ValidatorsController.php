<?php

namespace App\Controller\Fields;

use App\Entity\Field\Validator;
use App\Entity\Field\Field;
use App\Form\Field\ValidatorType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/fields")
 */
class ValidatorsController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{uuid}/validators/{page<\d+>?1}", name="field_validator_index", methods="GET")
     *
     * @param Field $field
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Field $field, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Validator::class)
            ->search(['field' => $field]);

        return $this->render('fields/validators/_index.html.twig', [
            'field' => $field,
            'paginator' => new Paginator($query, $page)
        ]);
    }

    /**
     * @Route("/{uuid}/validators/new", name="field_validator_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @param Field $field
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, Field $field): Http\Response
    {
        $validator = new Validator();
        $validator->setField($field);

        return $this->process($request, $validator);
    }

    /**
     * @Route("/validators/{uuid}/edit", name="field_validator_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Validator $validator
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Validator $validator): Http\Response
    {
        return $this->process($request, $validator);
    }

    /**
     * @Route("/validators/{uuid}/copy", name="field_validator_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Validator $validator
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Validator $validator): Http\Response
    {
        return $this->process($request, clone $validator);
    }

    /**
     * @Route("/validators/{uuid}", name="field_validator_delete", methods="DELETE")
     *
     * @param Validator $validator
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Validator $validator, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($validator, $this->getResponseData($validator->getField()));
    }

    /**
     * @param Field $field
     *
     * @return array
     */
    private function getResponseData(Field $field): array
    {
        return [
            'url' => $this->generateUrl('field_validator_index', ['uuid' => $field->getUuid()]),
            'container' => "#field-validators"
        ];
    }

    /**
     * @param Http\Request $request
     * @param Validator $validator
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Validator $validator): Http\Response
    {
        $isNewElement = $validator->getUuid() === null;

        $form = $this->createForm(ValidatorType::class, $validator);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($validator);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData($validator->getField()), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('fields/validators/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'validator' => $validator,
            'form' => $form->createView()
        ]);
    }
}
