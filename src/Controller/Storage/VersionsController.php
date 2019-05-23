<?php

namespace App\Controller\Storage;

use App\Entity\Storage\Storage;
use App\Entity\Storage\File;
use App\Entity\Storage\Version;
use App\Form\Storage\FileType;
use App\Service\DownloadService;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/storage")
 */
class VersionsController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{uuid}/versions/{page<\d+>?1}", name="storage_version_index", methods="GET")
     *
     * @param Storage $storage
     * @param int $page
     *
     * @return Http\Response
     */
    public function index(Storage $storage, int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Version::class)
            ->search(['storage' => $storage]);

        return $this->render('storage/versions/_index.html.twig', [
            'storage' => $storage,
            'paginator' => new Paginator($query, $page, 4)
        ]);
    }

    /**
     * @Route("/{uuid}/versions/upload", name="storage_version_upload", methods="PUT")
     *
     * @param Http\Request $request
     * @param Storage $storage
     *
     * @return Http\Response
     */
    public function upload(Http\Request $request, Storage $storage): Http\Response
    {
        $file = new File($request->request->all());
        $storage->addVersion($file);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($storage);
        $manager->flush();

        return new Http\JsonResponse(['file_uuid' => $file->getUuid()]);
    }

    /**
     * @Route("/versions/{uuid}/download", name="storage_version_download", methods="GET")
     *
     * @param Version $version
     * @param DownloadService $service
     *
     * @return Http\Response
     */
    public function download(Version $version, DownloadService $service): Http\Response
    {
        return $service->download($version->getFile());
    }

    /**
     * @Route("/versions/{uuid}/edit", name="storage_version_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Version $version
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Version $version): Http\Response
    {
        return $this->process($request, $version);
    }

    /**
     * @Route("/versions/{uuid}/activate", name="storage_version_activate", methods="POST")
     *
     * @param Version $version
     * @param EntityService $service
     *
     * @return Http\Response
     */
    public function activate(Version $version, EntityService $service): Http\Response
    {
        if (!$service->isRequestValid()) {
            return new Http\JsonResponse(['error' => 'Пароль указан неверно'], 400);
        }

        $version->setIsActive(true);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($version);
        $manager->flush();

        return new Http\JsonResponse([
            'url' => $this->generateUrl('storage_version_index', ['uuid' => $version->getStorage()->getUuid()]),
            'container' => '#storage-versions'
        ]);
    }

    /**
     * @Route("/versions/{uuid}", name="storage_version_delete", methods="DELETE")
     *
     * @param Version $version
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Version $version, EntityService $entity): Http\JsonResponse
    {
        return $entity->delete($version, $this->getResponseData($version->getStorage()));
    }

    /**
     * @param Storage $storage
     *
     * @return array
     */
    private function getResponseData(Storage $storage): array
    {
        return [
            'url' => $this->generateUrl('storage_version_index', ['uuid' => $storage->getUuid()]),
            'container' => '#storage-versions'
        ];
    }

    /**
     * @param Http\Request $request
     * @param Version $version
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Version $version): Http\Response
    {
        $file = $version->getFile();

        $form = $this->createForm(FileType::class, $file);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($file);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData($version->getStorage()), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('storage/versions/edit.html.twig', [
            'version' => $version,
            'form' => $form->createView(),
        ]);
    }
}
