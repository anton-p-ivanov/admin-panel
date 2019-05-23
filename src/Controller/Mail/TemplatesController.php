<?php

namespace App\Controller\Mail;

use App\Entity\Mail\Template;
use App\Form\MailTemplateType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mail/templates")
 */
class TemplatesController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{page<\d+>?1}", name="mail_template_index", methods="GET")
     *
     * @param Http\Request $request
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Http\Request $request, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Template::class)
            ->search($request->get('search'));

        $view = 'mail/templates/index.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'mail/templates/_index.html.twig';
        }

        return $this->render($view, [
            'paginator' => new Paginator($query, $page)
        ]);
    }

    /**
     * @Route("/new", name="mail_template_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @return Http\Response
     */
    public function new(Http\Request $request): Http\Response
    {
        return $this->process($request, new Template());
    }

    /**
     * @Route("/{uuid}/edit", name="mail_template_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Template $template
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Template $template): Http\Response
    {
        return $this->process($request, $template);
    }

    /**
     * @Route("/{uuid}/copy", name="mail_template_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Template $template
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Template $template): Http\Response
    {
        return $this->process($request, clone $template);
    }

    /**
     * @Route("/{uuid}", name="mail_template_delete", methods="DELETE")
     *
     * @param Template $template
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Template $template, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($template, $this->getResponseData());
    }

    /**
     * @return array
     */
    private function getResponseData(): array
    {
        return [
            'url' => $this->generateUrl('mail_template_index'),
            'container' => '#mail-templates'
        ];
    }

    /**
     * @param Http\Request $request
     * @param Template $template
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Template $template): Http\Response
    {
        $isNewElement = $template->getUuid() === null;

        $form = $this->createForm(MailTemplateType::class, $template);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($template);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData(), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('mail/templates/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'template' => $template,
            'form' => $form->createView(),
        ]);
    }
}