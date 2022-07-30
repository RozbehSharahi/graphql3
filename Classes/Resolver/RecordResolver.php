<?php

namespace RozbehSharahi\Graphql3\Resolver;

use RozbehSharahi\Graphql3\Exception\GraphqlException;
use TYPO3\CMS\Core\Database\ConnectionPool;

class RecordResolver
{
    public function __construct(protected ConnectionPool $connectionPool)
    {
    }

    public function resolve(string $table, ?int $uid): ?array
    {
        if (!$uid) {
            return null;
        }

        $query = $this->connectionPool->getQueryBuilderForTable($table);

        $query
            ->select('*')
            ->from($table)
            ->where($query->expr()->eq('uid', $query->createNamedParameter($uid, \PDO::PARAM_INT)))
        ;

        try {
            return $query->executeQuery()->fetchAssociative();
        } catch (\Exception) {
            return null;
        }
    }

    public function resolveManyByPid(string $table, int $parentPageId): array
    {
        $query = $this->connectionPool->getQueryBuilderForTable($table);

        $query
            ->select('*')
            ->from($table)
            ->where($query->expr()->eq('pid', $query->createNamedParameter($parentPageId, \PDO::PARAM_INT)))
        ;

        try {
            return $query->executeQuery()->fetchAllAssociative();
        } catch (\Exception) {
            throw new GraphqlException('Error fetching pages.');
        }
    }
}
