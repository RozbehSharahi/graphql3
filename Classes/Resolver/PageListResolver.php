<?php

namespace RozbehSharahi\Graphql3\Resolver;

use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Trait\ExecuteQueryTrait;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class PageListResolver
{
    use ExecuteQueryTrait;

    public function __construct(protected ConnectionPool $connectionPool)
    {
    }

    public function resolveItems(ListRequest $request): array
    {
        $query = $this->createQuery($request);

        return $this
            ->applyPagination($query, $request)
            ->applySorting($query, $request)
            ->fetchAll($query);
    }

    public function resolveCount(ListRequest $request): int
    {
        $query = $this->createQuery($request);

        return $this->fetchRowCount($query);
    }

    protected function createQuery(ListRequest $request): QueryBuilder
    {
        return $this
            ->connectionPool
            ->getQueryBuilderForTable('pages')
            ->select('*')
            ->from('pages');
    }

    private function applyPagination(QueryBuilder $query, ListRequest $request): self
    {
        $offset = ($request->getPage() - 1) * $request->getPageSize();
        $length = $request->getPageSize();

        $query->setFirstResult($offset)->setMaxResults($length);

        return $this;
    }

    private function applySorting(QueryBuilder $query, ListRequest $request): self
    {
        $query->addOrderBy('uid', 'ASC');

        return $this;
    }
}
