<?php

namespace App\Controller\Mail;

use App\Entity\Mail\Type;
use App\Form\MailTypeType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mail/types")
 */
class TypesController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{page<\d+>?1}", name="mail_type_index", methods="GET")
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
            ->search($request->get('search'));

        $view = 'mail/types/index.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'mail/types/_index.html.twig';
        }

        return $this->render($view, ['paginator' => new Paginator($query, $page)]);
    }

    /**
     * @Route("/new", name="mail_type_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @return Http\Response
     */
    public function new(Http\Request $request): Http\Response
    {
        return $this->process($request, new Type());
    }

    /**
     * @Route("/{uuid}/edit", name="mail_type_edit", methods="GET|POST")
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
     * @Route("/{uuid}/copy", name="mail_type_copy", methods="GET|POST")
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
     * @Route("/{uuid}", name="mail_type_delete", methods="DELETE")
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
            'url' => $this->generateUrl('mail_type_index'),
            'container' => '#mail-types'
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

        $form = $this->createForm(MailTypeType::class, $type);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($type);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData(), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('mail/types/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'type' => $type,
            'form' => $form->createView(),
        ]);
    }
}
