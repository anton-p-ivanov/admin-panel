<?php

namespace App\Controller\Admin;

use App\Entity\Site;
use App\Form\SiteType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/sites")
 */
class SitesController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{page<\d+>?1}", name="site_index", methods="GET")
     *
     * @param Http\Request $request
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Http\Request $request, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Site::class)
            ->search();

        $view = 'admin/sites/index.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'admin/sites/_index.html.twig';
        }

        return $this->render($view, ['paginator' => new Paginator($query, $page)]);
    }

    /**
     * @Route("/new", name="site_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @return Http\Response
     */
    public function new(Http\Request $request): Http\Response
    {
        return $this->process($request, new Site());
    }

    /**
     * @Route("/{uuid}/edit", name="site_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Site $site
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Site $site): Http\Response
    {
        return $this->process($request, $site);
    }

    /**
     * @Route("/{uuid}/copy", name="site_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Site $site
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Site $site): Http\Response
    {
        return $this->process($request, clone $site);
    }

    /**
     * @Route("/{uuid}", name="site_delete", methods="DELETE")
     *
     * @param Site $site
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Site $site, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($site, $this->getResponseData());
    }

    /**
     * @return array
     */
    private function getResponseData(): array
    {
        return [
            'url' => $this->generateUrl('site_index'),
            'container' => '#admin-sites'
        ];
    }

    /**
     * @param Http\Request $request
     * @param Site $site
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Site $site): Http\Response
    {
        $isNewElement = $site->getUuid() === null;

        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($site);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData(), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('admin/sites/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'site' => $site,
            'form' => $form->createView(),
        ]);
    }
}
