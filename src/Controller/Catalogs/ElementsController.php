<?php

namespace App\Controller\Catalogs;

use App\Entity\Catalog as Catalog;
use App\Entity\Field\Field;
use App\Entity\Price as Price;
use App\Entity\Workflow;
use App\Entity\WorkflowStatus;
use App\Form\Catalog\ElementType;
use App\Service\EntityService;
use App\Tools\Paginator;
use App\Traits\AjaxControllerTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/catalogs")
 */
class ElementsController extends AbstractController
{
    use AjaxControllerTrait;

    /**
     * @Route("/{uuid<[\w\-]{36}>}/elements/{page<\d+>?1}", name="catalog_element_index", methods="GET")
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
        $repository = $this->getDoctrine()->getRepository(Catalog\Tree::class);

        /* @var Catalog\Tree $node */
        $node = $repository->findOneBy(['uuid' => $uuid]);
        $catalog = $this->getCatalog($node);

        if ($view) {
            $viewFile = "catalogs/elements/$view.html.twig";
        }
        else {
            $viewFile = "catalogs/elements/index.html.twig";
            if ($request->isXmlHttpRequest()) {
                $viewFile = "catalogs/elements/_index.html.twig";
            }
        }

        return $this->render($viewFile, [
            'node' => $node,
            'catalog' => $catalog,
            'parent' => $node ? $node->getParent() : null,
            'path' => $node ? $repository->getPath($node) : [],
            'paginator' => new Paginator($repository->search($node, $catalog, $request->get('search')), $page, $view ? 10 : null)
        ]);
    }

    /**
     * @Route("/{uuid<[\w\-]{36}>}/new/{type}", name="catalog_element_new", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Catalog\Tree $tree
     * @param string $type
     *
     * @return Http\Response
     */
    public function new(Http\Request $request, Catalog\Tree $tree, string $type): Http\Response
    {
        if (!($catalog = $this->getCatalog($tree))) {
            throw new NotFoundHttpException('Invalid node');
        }

        $element = new Catalog\Element();
        $parentElement = $tree->getElement();

        $parentNodes = new ArrayCollection();
        $parentNodes->add($tree);

        $properties = [
            'parentNodes' => $parentNodes,
            'catalog' => $catalog,
            'type' => strtoupper(substr($type, 0, 1)),
            'workflow' => $this->getWorkflow($parentElement),
            'sites' => $parentElement ? $parentElement->getSites() : new ArrayCollection(),
            'roles' => $parentElement ? $parentElement->getRoles() : new ArrayCollection(),
        ];

        if ($catalog->isTrading()) {
            $properties['price'] = $this->getPrice();
        }

        foreach ($properties as $property => $value) {
            $element->{'set' . ucfirst($property)}($value);
        }

        $this->setValues($element);

        return $this->process($request, $element);
    }

    /**
     * @Route("/elements/{uuid<[\w\-]{36}>}/edit", name="catalog_element_edit", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Catalog\Element $element
     *
     * @return Http\Response
     */
    public function edit(Http\Request $request, Catalog\Element $element): Http\Response
    {
        if ($element->isSection() === false) {
            if ($element->getCatalog()->isTrading() && $element->getPrice() === null) {
                $element->setPrice($this->getPrice());
            }

            $this->setValues($element);
        }

        return $this->process($request, $element);
    }

    /**
     * @Route("/elements/{uuid<[\w\-]{36}>}/copy", name="catalog_element_copy", methods="GET|POST")
     *
     * @param Http\Request $request
     * @param Catalog\Element $element
     *
     * @return Http\Response
     */
    public function copy(Http\Request $request, Catalog\Element $element): Http\Response
    {
        $element->cloneWithAssociations = $request->get('deep', false);
        $clone = clone $element;

        // If no price has been set for cloned element set it as new price
        if ($element->getCatalog()->isTrading() && $clone->getPrice() === null) {
            $clone->setPrice($this->getPrice());
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->getRepository(Catalog\Tree::class)->setTreeNode($clone);

        return $this->redirectToRoute('catalog_element_edit', ['uuid' => $clone->getUuid()]);
    }

    /**
     * @Route("/elements/{uuid<[\w\-]{36}>}", name="catalog_element_delete", methods="DELETE")
     *
     * @param Catalog\Element $element
     * @param EntityService $entity
     *
     * @return Http\JsonResponse
     */
    public function delete(Catalog\Element $element, EntityService $entity): Http\JsonResponse
    {
        if (!$entity->isRequestValid()) {
            return new Http\JsonResponse(['error' => 'Пароль указан неверно'], 400);
        }

        $parentNodes = $element->getParentNodes();
        $uuid = $parentNodes->isEmpty() ? $element->getCatalog()->getTree() : $parentNodes->first()->getUuid();
        $response = [
            'url' => $this->generateUrl('catalog_element_index', ['uuid' => $uuid]),
            'container' => '#elements-list'
        ];

        $manager = $this->getDoctrine()->getManager();
        foreach ($element->getNodes() as $node) {
            $manager->remove($node);
            $manager->flush();
        }

        return new Http\JsonResponse($response);
    }

    /**
     * @param string $uuid
     *
     * @return array
     */
    private function getResponseData(string $uuid)
    {
        return [
            'url' => $this->generateUrl('catalog_element_index', ['uuid' => $uuid]),
            'container' => '#elements-list'
        ];
    }

    /**
     * @param Http\Request $request
     * @param Catalog\Element $element
     *
     * @return Http\Response
     */
    private function process(Http\Request $request, Catalog\Element $element): Http\Response
    {
        $isNewElement = $element->getUuid() === null;

        $fields = $price = null;
        $originalBrakes = new ArrayCollection();
        if ($element->isSection() === false) {
            if ($price = $element->getPrice()) {
                $originalBrakes = $price->getBrakes();
            }

            $fields = $this->getDoctrine()
                ->getRepository(Field::class)
                ->findAllAvailableByHash(strtoupper(md5(Catalog\Catalog::class.$element->getCatalog()->getUuid())));
        }

        $form = $this->createForm(ElementType::class, $element, ['fields' => $fields]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getManager();

                if ($price instanceof Price\Price) {
                    foreach ($originalBrakes as $brake) {
                        if (!$price->getBrakes()->contains($brake)) {
                            $manager->remove($brake);
                        }
                    }
                }

                $manager->getRepository(Catalog\Tree::class)->setTreeNode($element);

                return new Http\JsonResponse($this->getResponseData($element->getParentNodes()->first()->getUuid()), 200);
            }

            return new Http\JsonResponse($this->getFormErrors($form), 206);
        }

        // Store current route to return to
        $this->get('session')->set('referer', $request->getPathInfo());

        return $this->render('catalogs/elements/' . ($isNewElement ? 'new' : 'edit') . '.html.twig', [
            'element' => $element,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Catalog\Tree $node
     *
     * @return Catalog\Catalog|null
     */
    private function getCatalog(Catalog\Tree $node): ?Catalog\Catalog
    {
        if ($node->getLevel() === 0) {
            return $this->getDoctrine()
                ->getRepository(Catalog\Catalog::class)
                ->findOneBy(['tree' => $node]);
        }

        return $node->getElement()->getCatalog();
    }

    /**
     * @param Catalog\Element|null $element
     *
     * @return Workflow
     */
    private function getWorkflow(?Catalog\Element $element): Workflow
    {
        $workflow = new Workflow();
        $workflow->setStatus(
            $element
                ? $element->getWorkflow()->getStatus()
                : $this->getDoctrine()->getRepository(WorkflowStatus::class)->findOneBy(['isDefault' => true])
        );

        return $workflow;
    }

    /**
     * @return Price\Price
     */
    private function getPrice(): Price\Price
    {
        $doctrine = $this->getDoctrine();

        /* @var $currency Price\Currency */
        $currency = $doctrine->getRepository(Price\Currency::class)->findOneBy(['isDefault' => true]);

        /* @var $vat Price\Vat */
        $vat = $doctrine->getRepository(Price\Vat::class)->findOneBy(['isDefault' => true]);

        $price = new Price\Price();
        $price->setCurrency($currency);
        $price->setVat($vat);

        return $price;
    }

    /**
     * @param Catalog\Element $element
     */
    private function setValues(Catalog\Element $element)
    {
        $hash = strtoupper(md5(Catalog\Catalog::class . $element->getCatalog()->getUuid()));

        /* @var Field[] $fields */
        $fields = $this->getDoctrine()
            ->getRepository(Field::class)
            ->findAllAvailableByHash($hash);

        $values = new ArrayCollection();
        foreach ($fields as $field) {
            if ($element->getValues()->containsKey($field->getUuid())) {
                $value = $element->getValues()->get($field->getUuid());
            }
            else {
                $value = new Catalog\ElementValue();
                $value->setField($field);
                $value->setElement($element);
                $value->setValue($field->getValue());
            }

            $values->add($value);
        }

        $element->setValues($values);
    }
}
