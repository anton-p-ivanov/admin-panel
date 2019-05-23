<?php

namespace App\Controller\Admin\Addresses;

use App\Entity\Address;
use App\Entity\AddressType;
use App\Form\AddressTypeType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/addresses/types")
 */
class TypesController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{page<\d+>?1}", name="address_type_index", methods="GET")
     *
     * @param Http\Request $request
     * @param int|null $page
     *
     * @return Http\Response
     */
    public function index(Http\Request $request, ?int $page): Http\Response
    {
        $query = $this->getDoctrine()
            ->getRepository(AddressType::class)
            ->search();

        $view = 'admin/addresses/types/index.html.twig';
        if ($request->isXmlHttpRequest()) {
            $view = 'admin/addresses/types/_index.html.twig';
        }

        return $this->render($view, [
            'paginator' => new Paginator($query, $page)
        ]);
    }

    /**
     * @Route("/new", name="address_type_new", methods="GET|POST")
     * @param Http\Request $request
     *
     * @return Http\Response
     */
    public function new(Http\Request $request): Http\Response
    {
        return $this->process($request, new AddressType());
    }

    /**
     * @Route("/{uuid}/edit", name="address_type_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param AddressType $addressType
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, AddressType $addressType): Http\Response
    {
        return $this->process($request, $addressType);
    }

    /**
     * @Route("/{uuid}/copy", name="address_type_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param AddressType $addressType
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, AddressType $addressType): Http\Response
    {
        return $this->process($request, clone $addressType);
    }

    /**
     * @Route("/{uuid}", name="address_type_delete", methods="DELETE")
     *
     * @param AddressType $addressType
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(AddressType $addressType, EntityService $entity): Http\JsonResponse
    {
        if ($addressType->isDefault()) {
            $message = "Тип адреса с параметром \"По-умолчанию\" не может быть удален.";
            return new Http\JsonResponse(['error' => $message], 400);
        }

        $recordsCount = $this->getDoctrine()
            ->getRepository(Address::class)
            ->count(['type' => $addressType]);

        if ($recordsCount > 0) {
            $message = "Этот тип адреса нельзя удалить, так как найдены связанные с ним адреса.";
            return new Http\JsonResponse(['error' => $message], 400);
        }

        return $entity->delete($addressType, $this->getResponseData());
    }

    /**
     * @return array
     */
    private function getResponseData(): array
    {
        return [
            'url' => $this->generateUrl('address_type_index'),
            'container' => '#address-types'
        ];
    }

    /**
     * @param Http\Request $request
     * @param AddressType $addressType
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, AddressType $addressType): Http\Response
    {
        $isNewElement = $addressType->getUuid() === null;

        $form = $this->createForm(AddressTypeType::class, $addressType);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($addressType);
                $manager->flush();

                return new Http\JsonResponse($this->getResponseData(), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        return $this->render('admin/addresses/types/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'type' => $addressType,
            'form' => $form->createView(),
        ]);
    }
}
