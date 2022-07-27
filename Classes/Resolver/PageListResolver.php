<?php

namespace RozbehSharahi\Graphql3\Resolver;

use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Exception\GraphqlException;
use RozbehSharahi\Graphql3\Operator\ApplyFilterArrayToQueryOperator;
use RozbehSharahi\Graphql3\Trait\ExecuteQueryTrait;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class PageListResolver
{
    use ExecuteQueryTrait;

    public function __construct(
        protected ConnectionPool $connectionPool,
        protected ApplyFilterArrayToQueryOperator $applyFilterArrayToQueryOperator
    ) {
    }

    public function resolveItems(ListRequest $request): array
    {
        $query = $this->createQuery($request);

        return $this
            ->applyFilters($query, $request)
            ->applyPagination($query, $request)
            ->applySorting($query, $request)
            ->fetchAll($query);
    }

    public function resolveCount(ListRequest $request): int
    {
        $query = $this->createQuery($request);

        return $this
            ->applyFilters($query, $request)
            ->fetchRowCount($query);
    }

    protected function createQuery(ListRequest $request): QueryBuilder
    {
        return $this
            ->connectionPool
            ->getQueryBuilderForTable('pages')
            ->select('*')
            ->from('pages');
    }

    private function applyFilters(QueryBuilder $query, ListRequest $request): self
    {
        if (empty($request->getFilters())) {
            return $this;
        }

        $this->applyFilterArrayToQueryOperator->operate($query, $request->getFilters());

        return $this;
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
        foreach ($request->getOrderBy() as $orderItem) {
            $this->assertOrderItemValid($orderItem);
            $query->addOrderBy($orderItem['field'], $orderItem['direction']);
        }

        return $this;
    }

    private function assertOrderItemValid(array $orderItem): self
    {
        if (empty($orderItem['field'])) {
            throw GraphqlException::createClientSafe('Order item is not valid.');
        }

        if (!empty($orderItem['direction']) && !in_array(strtolower($orderItem['direction']), ['desc', 'asc'])) {
            throw GraphqlException::createClientSafe('Order item is not valid.');
        }

        return $this;
    }
}
