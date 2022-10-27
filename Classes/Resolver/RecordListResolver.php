<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Resolver;

use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use RozbehSharahi\Graphql3\Exception\GraphqlException;
use RozbehSharahi\Graphql3\Operator\ApplyFilterArrayToQueryOperator;
use RozbehSharahi\Graphql3\Security\AccessChecker;
use RozbehSharahi\Graphql3\Site\CurrentSite;
use RozbehSharahi\Graphql3\Trait\ExecuteQueryTrait;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class RecordListResolver
{
    use ExecuteQueryTrait;

    protected string $table;

    public function __construct(
        protected ConnectionPool $connectionPool,
        protected ApplyFilterArrayToQueryOperator $applyFilterArrayToQueryOperator,
        protected AccessChecker $accessChecker,
        protected CurrentSite $currentSite
    ) {
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function for(string $table): self
    {
        $clone = clone $this;
        $clone->table = $table;

        return $clone;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function resolveItems(ListRequest $request): array
    {
        if (!$this->table) {
            throw new GraphqlException('Table was not set. Did you forget to call ->for?');
        }

        $query = $this->createQuery();

        $records = $this
            ->applyFilters($query, $request)
            ->applyLanguageFilter($query, $request)
            ->applyPublicRequestFilters($query, $request)
            ->applyPagination($query, $request)
            ->applySorting($query, $request)
            ->applyListRequestModification($query, $request)
            ->fetchAll($query)
        ;

        foreach ($records as $record) {
            $this->accessChecker->assert(['VIEW'], Record::create($this->table, $record));
        }

        return $records;
    }

    public function resolveCount(ListRequest $request): int
    {
        $query = $this->createQuery();

        return $this
            ->applyFilters($query, $request)
            ->applyLanguageFilter($query, $request)
            ->applyListRequestModification($query, $request)
            ->applyPublicRequestFilters($query, $request)
            ->fetchRowCount($query)
        ;
    }

    protected function createQuery(): QueryBuilder
    {
        return $this
            ->connectionPool
            ->getQueryBuilderForTable($this->table)
            ->select('*')
            ->from($this->table)
        ;
    }

    protected function applyPublicRequestFilters(QueryBuilder $query, ListRequest $request): self
    {
        if (!$request->isPublicRequest()) {
            return $this;
        }

        $config = TableConfiguration::create($this->table);

        if (!$config->hasAccessControl()) {
            return $this;
        }

        $query->andWhere($query->expr()->or(
            $query->expr()->eq($config->getAccessControl(), 0),
            $query->expr()->eq($config->getAccessControl(), '""'),
            $query->expr()->isNull($config->getAccessControl())
        ));

        return $this;
    }

    protected function applyFilters(QueryBuilder $query, ListRequest $request): self
    {
        if (empty($request->getFilters())) {
            return $this;
        }

        $this->applyFilterArrayToQueryOperator->operate($query, $request->getFilters());

        return $this;
    }

    private function applyLanguageFilter(QueryBuilder $query, ListRequest $request): self
    {
        if (empty($request->getLanguage())) {
            return $this;
        }

        if (!$this->currentSite->isLanguageCodeAvailable($request->getLanguage())) {
            throw GraphqlException::createClientSafe('Given language code is not available on current site.');
        }

        $language = $this->currentSite->getLanguageByCode($request->getLanguage());

        $query->andWhere($query->expr()->eq('sys_language_uid', $language->getLanguageId()));

        return $this;
    }

    protected function applyPagination(QueryBuilder $query, ListRequest $request): self
    {
        $offset = ($request->getPage() - 1) * $request->getPageSize();
        $length = $request->getPageSize();

        $query->setFirstResult($offset)->setMaxResults($length);

        return $this;
    }

    protected function applySorting(QueryBuilder $query, ListRequest $request): self
    {
        foreach ($request->getOrderBy() as $orderItem) {
            $this->assertOrderItemValid($orderItem);
            $query->addOrderBy($orderItem['field'], $orderItem['direction']);
        }

        return $this;
    }

    protected function applyListRequestModification(QueryBuilder $query, ListRequest $request): self
    {
        $request->getQueryModifier()($query);

        return $this;
    }

    /**
     * @param array<string, string> $orderItem
     *
     * @return $this
     */
    protected function assertOrderItemValid(array $orderItem): self
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
