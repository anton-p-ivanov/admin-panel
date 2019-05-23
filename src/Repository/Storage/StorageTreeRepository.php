<?php

namespace App\Repository\Storage;

use App\Entity\Storage\Storage;
use App\Entity\Storage\Tree;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * Class StorageTreeRepository
 *
 * @package App\Repository\Storage
 */
class StorageTreeRepository extends NestedTreeRepository
{
    /**
     * @param Tree|null $parentNode
     * @param null|string $search
     *
     * @return \Doctrine\ORM\Query
     */
    public function search(?Tree $parentNode = null, ?string $search = null): Query
    {
        $queryBuilder = $this->getRootNodesQueryBuilder();
        if ($parentNode) {
            $queryBuilder = $this->getChildrenQueryBuilder($parentNode, true);
        }

        $conditions = $queryBuilder->expr()->andX();

        if ($search) {
            $queryBuilder = $this->createQueryBuilder('node');
            $conditions->add('s.type = :type');
            $conditions->add('s.title LIKE :search OR s.description LIKE :search OR f.name LIKE :search');
            $queryBuilder->setParameter('type', Storage::STORAGE_TYPE_FILE);
            $queryBuilder->setParameter('search', "%$search%");
        }

        $conditions->add('w.uuid IS NULL OR w.isDeleted = :isDeleted');
        $queryBuilder->setParameter('isDeleted', false);

        return $queryBuilder
            ->addSelect(['s', 'w', 'v', 'f'])
            ->innerJoin('node.storage', 's')
            ->leftJoin('s.versions', 'v')
            ->leftJoin('v.file', 'f')
            ->leftJoin('s.workflow', 'w')
            ->andWhere($conditions)
            ->orderBy('s.type', 'ASC')
            ->addOrderBy('s.title', 'ASC')
            ->getQuery();
    }

    /**
     * @param mixed $exclude
     *
     * @return QueryBuilder
     */
    public function getTreeQueryBuilder($exclude = null)
    {
        $qb = $this->createQueryBuilder('t');

        $predicates = $qb->expr()->andX();
        $predicates->add($qb->expr()->eq('s.type', ':type'));
        $qb->setParameter('type', Storage::STORAGE_TYPE_DIR);

        if ($exclude && $exclude->getUuid()) {
            $predicates->add($qb->expr()->notIn('t.storage', ':exclude'));
            $qb->setParameter('exclude', [$exclude]);
        }

        $qb
            ->select(['t', 's'])
            ->innerJoin('t.storage', 's')
            ->where($predicates)
            ->addOrderBy('t.root', 'ASC')
            ->addOrderBy('t.leftMargin', 'ASC');

        return $qb;
    }

    /**
     * @param Storage $element
     */
    public function setTreeNode(Storage $element)
    {
        $isNewElement = $element->getUuid() === null;
        $this->getEntityManager()->persist($element);

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
     * @param Storage $element
     *
     * @return bool
     */
    private function isNodesChanged(Storage $element): bool
    {
        $previousNodes = $this->findBy(['storage' => $element]);
        $parentNodes = $element->getParentNodes()->toArray();

        $sourceArray = array_map(function(Tree $node) { return $node->getParent() ? $node->getParent()->getUuid() : null; }, $previousNodes);
        $compareArray = array_map(function(Tree $node) { return $node->getUuid(); }, $parentNodes);

        return $sourceArray !== $compareArray;
    }

    /**
     * @param Storage $element
     */
    private function insertNodes(Storage $element)
    {
        $parentNodes = $element->getParentNodes();

        if ($element->isDirectory() && $parentNodes->count() > 1) {
            $parentNodes = new ArrayCollection([$parentNodes->first()]);
            $element->setParentNodes($parentNodes);
        }

        if ($parentNodes->isEmpty()) {
            $node = new Tree();
            $node->setStorage($element);
            $node->setParent(null);
            $this->getEntityManager()->persist($node);
        }
        else {
            foreach ($parentNodes as $parentNode) {
                $node = new Tree();
                $node->setStorage($element);
                $node->setParent($parentNode);
                $this->getEntityManager()->persist($node);
            }
        }
    }

    /**
     * @param Storage $element
     */
    private function updateNodes(Storage $element)
    {
        if ($element->isDirectory()) {
            if ($parent = $element->getParentNodes()->first()) {
                $this->persistAsFirstChildOf($element->getNodes()->first(), $parent);
            }
            else {
                $this->persistAsRoot($element->getNodes()->first());
            }
        }
        else {
            $previousNodes = $this->findBy(['storage' => $element]);
            if ($previousNodes) {
                foreach ($previousNodes as $node) {
                    $this->removeFromTree($node);
                }
            }

            $this->insertNodes($element);
        }
    }

    /**
     * @param Tree $node
     */
    protected function persistAsRoot(Tree $node)
    {
        $root = new Tree();
        $root->setStorage($node->getStorage());
        $this->getEntityManager()->persist($root);

        $children = $node->getChildren();
        foreach ($children as $child) {
            $child->setParent($root);
            $this->getEntityManager()->persist($child);
        }

        $this->removeFromTree($node);
    }
}
