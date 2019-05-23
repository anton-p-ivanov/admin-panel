<?php

namespace App\Controller\Catalogs;

use App\Entity\Catalog\Catalog;
use App\Entity\Catalog\Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/catalogs")
 */
class CatalogsController extends AbstractController
{
    /**
     * @Route("/", name="catalog_dashboard", methods="GET")
     *
     * @return Response
     */
    public function dashboard(): Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Type::class)
            ->search();

        return $this->render('catalogs/catalogs/dashboard.html.twig', [
            'types' => $query->getResult()
        ]);
    }

    /**
     * @Route("/{uuid<[\w\-]{36}>}", name="catalog_index", methods="GET")
     *
     * @param Type $type
     *
     * @return Response
     */
    public function index(Type $type): Response
    {
        $query = $this->getDoctrine()
            ->getRepository(Catalog::class)
            ->search(['type' => $type]);

        return $this->render('catalogs/catalogs/index.html.twig', [
            'catalogs' => $query->getResult(),
            'type' => $type
        ]);
    }
}
