<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Resolver;

use Doctrine\DBAL\Exception;
use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use RozbehSharahi\Graphql3\Exception\BadRequestException;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Operator\ApplyFilterArrayToQueryOperator;
use RozbehSharahi\Graphql3\Security\AccessChecker;
use RozbehSharahi\Graphql3\Site\CurrentSite;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class RecordListResolver
{
    protected TableConfiguration $table;

    public function __construct(
        protected ConnectionPool $connectionPool,
        protected ApplyFilterArrayToQueryOperator $applyFilterArrayToQueryOperator,
        protected AccessChecker $accessChecker,
        protected CurrentSite $currentSite
    ) {
    }

    public function getTable(): TableConfiguration
    {
        return $this->table;
    }

    public function for(string|TableConfiguration $table): self
    {
        $clone = clone $this;

        if (is_string($table)) {
            $table = TableConfiguration::create($table);
        }

        $clone->table = $table;

        return $clone;
    }

    /**
     * @return array<int, Record>
     */
    public function resolveItems(ListRequest $request): array
    {
        if (empty($this->table)) {
            throw new InternalErrorException('Table was not set. Did you forget to call ->for?');
        }

        $query = $this->createQuery();

        $this
            ->applyFilters($query, $request)
            ->applyLanguageFilter($query, $request)
            ->applyPublicRequestFilters($query, $request)
            ->applyPagination($query, $request)
            ->applySorting($query, $request)
            ->applyListRequestModification($query, $request)
        ;

        try {
            $rows = $query->executeQuery()->fetchAllAssociative();
        } catch (Exception $e) {
            throw new InternalErrorException('Could not fetch rows on record-list-resolving: '.$e->getMessage());
        }

        $records = array_map(fn (array $row) => Record::create($this->table, $row), $rows);

        foreach ($records as $record) {
            $this->accessChecker->assert(['VIEW'], $record);
        }

        return $records;
    }

    public function resolveCount(ListRequest $request): int
    {
        $query = $this->createQuery();

        $this
            ->applyFilters($query, $request)
            ->applyLanguageFilter($query, $request)
            ->applyListRequestModification($query, $request)
            ->applyPublicRequestFilters($query, $request)
        ;

        try {
            return $query->selectLiteral('count(*) as count')->executeQuery()->fetchAssociative()['count'];
        } catch (Exception $e) {
            throw new InternalErrorException('Row counting failed on record-list-resolving: '.$e->getMessage());
        }
    }

    protected function createQuery(): QueryBuilder
    {
        return $this
            ->connectionPool
            ->getQueryBuilderForTable($this->table->getName())
            ->select('*')
            ->from($this->table->getName())
        ;
    }

    protected function applyPublicRequestFilters(QueryBuilder $query, ListRequest $request): self
    {
        if (!$request->isPublicRequest()) {
            return $this;
        }

        if (!$this->table->hasAccessControl()) {
            return $this;
        }

        $query->andWhere($query->expr()->or(
            $query->expr()->eq($this->table->getAccessControl(), 0),
            $query->expr()->eq($this->table->getAccessControl(), '""'),
            $query->expr()->isNull($this->table->getAccessControl())
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
            throw new BadRequestException('Given language code is not available on current site.');
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
            throw new BadRequestException('Order item "field" was not set.');
        }

        if (!empty($orderItem['direction']) && !in_array(strtolower($orderItem['direction']), ['desc', 'asc'])) {
            throw new BadRequestException('Order item direction must be "desc" or "asc".');
        }

        return $this;
    }
}
