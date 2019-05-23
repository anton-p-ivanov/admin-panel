<?php

namespace App\Controller\Fields;

use App\Entity\Field\Field;
use App\Entity\Field\Value;
use App\Form\Field\ValueType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/fields")
 */
class ValuesController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{uuid}/values/{page<\d+>?1}", name="field_value_index", methods="GET")
     *
     * @param Field $field
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Field $field, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Value::class)
            ->search(['field' => $field]);

        return $this->render('fields/values/_index.html.twig', [
            'field' => $field,
            'paginator' => new Paginator($query, $page)
        ]);
    }

    /**
     * @Route("/{uuid}/values/new", name="field_value_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @param Field $field
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, Field $field): Http\Response
    {
        $value = new Value();
        $value->setField($field);

        return $this->process($request, $value);
    }

    /**
     * @Route("/values/{uuid}/edit", name="field_value_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Value $value
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Value $value): Http\Response
    {
        return $this->process($request, $value);
    }

    /**
     * @Route("/values/{uuid}/copy", name="field_value_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Value $value
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Value $value): Http\Response
    {
        return $this->process($request, clone $value);
    }

    /**
     * @Route("/values/{uuid}", name="field_value_delete", methods="DELETE")
     *
     * @param Value $value
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Value $value, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($value, $this->getResponseData($value->getField()));
    }

    /**
     * @param Field $field
     *
     * @return array
     */
    private function getResponseData(Field $field): array
    {
        return [
            'url' => $this->generateUrl('field_value_index', ['uuid' => $field->getUuid()]),
            'container' => "#field-values"
        ];
    }

    /**
     * @param Http\Request $request
     * @param Value $value
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Value $value): Http\Response
    {
        $isNewElement = $value->getUuid() === null;

        $form = $this->createForm(ValueType::class, $value);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($value);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData($value->getField()), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('fields/values/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'value' => $value,
            'form' => $form->createView()
        ]);
    }
}
