<?php

namespace App\Repository\Catalog;

use App\Entity\Catalog\Catalog;
use App\Entity\Catalog\Element;
use App\Entity\Catalog\Tree;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * Class CatalogTreeRepository
 *
 * @package App\Repository\Catalog
 */
class CatalogTreeRepository extends NestedTreeRepository
{
    /**
     * @param Tree $parentNode
     * @param Catalog $catalog
     * @param null|string $search
     *
     * @return \Doctrine\ORM\Query
     */
    public function search(Tree $parentNode, Catalog $catalog, ?string $search): Query
    {
        if ($search) {
            $queryBuilder = $this->searchInternal($search, $catalog);
        }
        else {
            $queryBuilder = $this->getRootNodesQueryBuilder();
            if ($parentNode) {
                $queryBuilder = $this->getChildrenQueryBuilder($parentNode, true);
            }
        }

        return $queryBuilder
            ->addSelect(['s', 'w'])
            ->innerJoin('node.element', 's')
            ->leftJoin('s.workflow', 'w')
            ->andWhere('w.uuid IS NULL OR w.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false)
            ->orderBy('s.type', 'DESC')
            ->addOrderBy('s.sort', 'ASC')
            ->addOrderBy('s.title', 'ASC')
            ->getQuery();
    }

    /**
     * @param string $search
     * @param Catalog $catalog
     *
     * @return QueryBuilder
     */
    private function searchInternal(string $search, Catalog $catalog)
    {
        $queryBuilder = $this->createQueryBuilder('node');

        $conditions = $queryBuilder->expr()->andX();
        $conditions->add('s.type = :type');
        $conditions->add('s.title LIKE :search OR s.description LIKE :search');
        $conditions->add('s.catalog = :catalog');

        return $queryBuilder
            ->groupBy('s.uuid')
            ->andWhere($conditions)
            ->setParameters([
                'type' => Element::TYPE_ELEMENT,
                'search' => "%$search%",
                'catalog' => $catalog
            ]);
    }

    /**
     * @param Tree $parentNode
     * @param mixed $exclude
     *
     * @return QueryBuilder
     */
    public function getTreeQueryBuilder(Tree $parentNode, $exclude = null)
    {
        $qb = $this->getChildrenQueryBuilder($parentNode);

        $predicates = $qb->expr()->andX();
        $predicates->add($qb->expr()->eq('s.type', ':type'));
        $qb->setParameter('type', Element::TYPE_SECTION);

        if ($exclude && $exclude->getUuid()) {
            $predicates->add($qb->expr()->notIn('node.element', ':exclude'));
            $qb->setParameter('exclude', [$exclude]);
        }

        $qb
            ->addSelect(['s'])
            ->innerJoin('node.element', 's')
            ->andWhere($predicates);

        return $qb;
    }

    /**
     * @param Element $element
     */
    public function setTreeNode(Element $element)
    {
        $isNewElement = $element->getUuid() === null;
        $this->getEntityManager()->persist($element);

        $parentNodes = $element->getParentNodes();
        if ($parentNodes->isEmpty()) {
            $parentNodes->add($element->getCatalog()->getTree());
            $element->setParentNodes($parentNodes);
        }

        if (!$isNewElement) {
            if ($this->isNodesChanged($element)) {
                $this->updateNodes($element);
            }
        }
        else {
            $this->insertNodes($element);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @param Element $element
     *
     * @return bool
     */
    private function isNodesChanged(Element $element): bool
    {
        $previousNodes = $this->findBy(['element' => $element]);
        $parentNodes = $element->getParentNodes()->toArray();

        $sourceArray = array_map(function(Tree $node) { return $node->getParent()->getUuid(); }, $previousNodes);
        $compareArray = array_map(function(Tree $node) { return $node->getUuid(); }, $parentNodes);

        return $sourceArray !== $compareArray;
    }

    /**
     * @param Element $element
     */
    private function insertNodes(Element $element)
    {
        $parentNodes = $element->getParentNodes();

        if ($element->isSection() && $parentNodes->count() > 1) {
            $parentNodes = new ArrayCollection([$parentNodes->first()]);
            $element->setParentNodes($parentNodes);
        }

        foreach ($parentNodes as $parentNode) {
            $node = new Tree();
            $node->setElement($element);
            $node->setParent($parentNode);
            $this->getEntityManager()->persist($node);
        }
    }

    /**
     * @param Element $element
     */
    private function updateNodes(Element $element)
    {
        $previousNodes = $this->findBy(['element' => $element]);
        if ($element->isSection()) {
            $this->persistAsFirstChildOf($element->getNodes()->first(), $element->getParentNodes()->first());
        }
        else {
            if ($previousNodes) {
                foreach ($previousNodes as $node) {
                    $this->removeFromTree($node);
                }
            }

            $this->insertNodes($element);
        }
    }
}
