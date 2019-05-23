<?php

namespace App\Controller\Storage;

use App\Entity\Storage\Storage;
use App\Entity\Storage\File;
use App\Entity\Storage\Tree;
use App\Form\Storage\StorageType;
use App\Service\DownloadService;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/storage")
 */
class StorageController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{uuid?root}/{page<\d+>?1}", name="storage_index", methods="GET")
     *
     * @param Http\Request $request
     * @param string $uuid
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Http\Request $request, string $uuid, int $page): Http\Response
    {
        $view = $request->get('view');
        $repository = $this->getDoctrine()->getRepository(Tree::class);

        /* @var $node Tree */
        $node = $repository->findOneBy(['uuid' => $uuid]);

        if ($view) {
            $viewFile = "storage/storage/$view.html.twig";
        }
        else {
            $viewFile = "storage/storage/index.html.twig";
            if ($request->isXmlHttpRequest()) {
                $viewFile = "storage/storage/_index.html.twig";
            }
        }

        return $this->render($viewFile, [
            'node' => $node,
            'parent' => $node ? $node->getParent() : null,
            'path' => $node ? $repository->getPath($node) : [],
            'paginator' => new Paginator($repository->search($node, $request->get('search')), $page, $view ? 10 : null)
        ]);
    }

    /**
     * @Route("/{uuid?root}/new", name="storage_new", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Tree|null $node
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, ?Tree $node): Http\Response
    {
        $storage = new Storage();

        $parentNodes = new ArrayCollection();
        if ($node) {
            $parentNodes->add($node);
            $storage->setParentNodes($parentNodes);
            $storage->setRoles($node->getStorage()->getRoles());
        }

        return $this->process($request, $storage);
    }

    /**
     * @Route("/{uuid?root}/upload", name="storage_upload", methods="PUT")
     *
     * @param Http\Request $request
     * @param Tree|null $node
     *
     * @return Http\Response
     */
    public function upload(Http\Request $request, ?Tree $node): Http\Response
    {
        $file = new File($request->request->all());
        $storage = new Storage([
            'type' => Storage::STORAGE_TYPE_FILE,
            'title' => $file->getName(),
        ]);

        $parentNodes = new ArrayCollection();
        if ($node) {
            $parentNodes->add($node);
            $storage->setParentNodes($parentNodes);
            $storage->setRoles($node->getStorage()->getRoles());
        }

        $storage->addVersion($file);

        $manager = $this->getDoctrine()->getManager();
        $manager->getRepository(Tree::class)->setTreeNode($storage);

        return new Http\JsonResponse(['file_uuid' => $file->getUuid()]);
    }

    /**
     * @Route("/{uuid}/download", name="storage_download", methods="GET")
     *
     * @param Storage $storage
     * @param DownloadService $service
     *
     * @return Http\Response
     */
    public function download(Storage $storage, DownloadService $service): Http\Response
    {
        return $service->download($storage->getFile());
    }

    /**
     * @Route("/{uuid}/edit", name="storage_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Storage $storage
     *
     * @return Http\Response
     * @todo Add storage categories management for files. Entities and data already exist.
     */
    public function edit(Http\Request $request, Storage $storage): Http\Response
    {
        return $this->process($request, $storage);
    }

    /**
     * @Route("/{uuid}", name="storage_delete", methods="DELETE")
     *
     * @param Tree $node
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Tree $node, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($node, $this->getResponseData($node->getParent()));
    }

    /**
     * @param Tree|null $node
     *
     * @return array
     */
    private function getResponseData(?Tree $node): array
    {
        return [
            'url' => $this->generateUrl('storage_index', ['uuid' => $node ? $node->getUuid() : null]),
            'container' => '#storage-list'
        ];
    }

    /**
     * @param Http\Request $request
     * @param Storage $storage
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Storage $storage): Http\Response
    {
        $isNewElement = $storage->getUuid() === null;

        $form = $this->createForm(StorageType::class, $storage);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->getRepository(Tree::class)->setTreeNode($storage);

                return new Http\JsonResponse($this->getResponseData($storage->getParentNodes()->count() ? $storage->getParentNodes()->first() : null), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('storage/storage/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'storage' => $storage,
            'form' => $form->createView(),
        ]);
    }
}
