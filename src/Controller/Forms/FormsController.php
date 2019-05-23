<?php

namespace App\Controller\Forms;

use App\Entity\Form\Form;
use App\Form\Form\FormType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/forms")
 */
class FormsController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{page<\d+>?1}", name="form_index", methods="GET")
     *
     * @param Http\Request $request
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Http\Request $request, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Form::class)
            ->search($request->get('search'));

        $view = 'forms/forms/index.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'forms/forms/_index.html.twig';
        }

        return $this->render($view, [
            'paginator' => new Paginator($query, $page)
        ]);
    }

    /**
     * @Route("/new", name="form_new", methods="GET|POST")
     *
     * @return Http\Response
     */
    public function new(): Http\Response
    {
        $form = new Form();
        $form->setTitle('Новая веб-форма');

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($form);
        $manager->flush();

        return $this->redirectToRoute('form_edit', ['uuid' => $form->getUuid()]);
    }

    /**
     * @Route("/{uuid}/edit", name="form_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Form $form
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Form $form): Http\Response
    {
        return $this->process($request, $form);
    }

    /**
     * @Route("/{uuid}/copy", name="form_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Form $form
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Form $form): Http\Response
    {
        if ($request->get('deep')) {
            $form->cloneWithAssociations = true;
        }

        $clone = clone $form;

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($clone);
        $manager->flush();

        if ($form->cloneWithAssociations) {
            $this->copyFields($form, $clone);
        }

        return $this->redirectToRoute('form_edit', ['uuid' => $clone->getUuid()]);
    }

    /**
     * @param Form $original
     * @param Form $clone
     */
    private function copyFields(Form $original, Form $clone)
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
     * @Route("/{uuid}", name="form_delete", methods="DELETE")
     *
     * @param Form $form
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Form $form, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($form, $this->getResponseData());
    }

    /**
     * @return array
     */
    private function getResponseData(): array
    {
        return [
            'url' => $this->generateUrl('form_index'),
            'container' => '#forms-list'
        ];
    }

    /**
     * @param Http\Request $request
     * @param Form $entity
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Form $entity): Http\Response
    {
        $isNewElement = $entity->getUuid() === null;

        $form = $this->createForm(FormType::class, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($entity);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData(), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('forms/forms/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
    }
}
