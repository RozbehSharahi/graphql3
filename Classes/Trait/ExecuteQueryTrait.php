<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Trait;

use Doctrine\DBAL\Exception;
use RozbehSharahi\Graphql3\Exception\GraphqlException;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

trait ExecuteQueryTrait
{
    /**
     * @return array<string, mixed>
     */
    public function fetch(QueryBuilder $query): array
    {
        try {
            return $query->executeQuery()->fetchAssociative();
        } catch (Exception $e) {
            throw new GraphqlException('Fetch failed: '.$e->getMessage());
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll(QueryBuilder $query): array
    {
        try {
            return $query->executeQuery()->fetchAllAssociative();
        } catch (Exception $e) {
            throw new GraphqlException('Fetch all failed: '.$e->getMessage());
        }
    }

    public function fetchRowCount(QueryBuilder $query): int
    {
        try {
            return $query->selectLiteral('count(*) as count')->executeQuery()->fetchAssociative()['count'];
        } catch (Exception $e) {
            throw new GraphqlException('Row count failed: '.$e->getMessage());
        }
    }
}
