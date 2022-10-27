<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Service;

use Doctrine\DBAL\Exception;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Exception\GraphqlException;
use TYPO3\CMS\Core\Database\ConnectionPool;

class DataService
{
    public function __construct(protected ConnectionPool $connectionPool)
    {
    }

    public function getRecordByUid(string $table, int $uid): ?Record
    {
        $query = $this->connectionPool->getQueryBuilderForTable($table);
        $query
            ->select('*')
            ->from($table)
            ->where($query->expr()->eq('uid', $uid))
        ;

        try {
            $row = $query->executeQuery()->fetchAssociative();
        } catch (Exception $e) {
            throw GraphqlException::createClientSafe("Could not fetch record by identifier: {$table}::{$uid}");
        }

        return $row ? Record::create($table, $row) : null;
    }
}
