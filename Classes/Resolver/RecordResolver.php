<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Resolver;

use RozbehSharahi\Graphql3\Domain\Model\ItemRequest;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Security\AccessChecker;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class RecordResolver
{
    protected TableConfiguration $table;

    public function __construct(
        protected ConnectionPool $connectionPool,
        protected AccessChecker $accessChecker,
    ) {
    }

    public function getTable(): TableConfiguration
    {
        return $this->table;
    }

    public function for(TableConfiguration|string $table): self
    {
        $clone = clone $this;

        if (is_string($table)) {
            $table = TableConfiguration::create($table);
        }

        $clone->table = $table;

        return $clone;
    }

    public function resolve(ItemRequest $request): ?Record
    {
        if (empty($this->table)) {
            throw new InternalErrorException('No table given, did you forget to call ->for?');
        }

        $identifier = $request->get('uid');

        if (!$identifier) {
            return null;
        }

        $query = $this->createQuery();
        $query->where($query->expr()->eq('uid', $query->createNamedParameter($identifier)));
        $this->applyPublicRequestFilters($query, $request);

        try {
            $row = $query->executeQuery()->fetchAssociative();
        } catch (\Throwable $e) {
            throw new InternalErrorException('Error on fetching page from database :'.$e->getMessage());
        }

        if (empty($row)) {
            return null;
        }

        $record = Record::create($this->table, $row);

        $this->accessChecker->assert(['VIEW'], $record);

        return $record;
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

    protected function applyPublicRequestFilters(QueryBuilder $query, ItemRequest $request): self
    {
        if (!$request->isPublicRequest()) {
            return $this;
        }

        $config = TableConfiguration::create($this->table->getName());

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
}
