<?php

namespace RozbehSharahi\Graphql3\Resolver;

use RozbehSharahi\Graphql3\Exception\GraphqlException;
use RozbehSharahi\Graphql3\Node\PageNodeExtenderInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class PageResolver
{
    /**
     * @param iterable<PageNodeExtenderInterface> $extenders
     */
    public function __construct(protected ConnectionPool $connectionPool, protected iterable $extenders)
    {
    }

    public function getCallable(): callable
    {
        return function ($_, $arguments) {
            return $this->resolve($arguments);
        };
    }

    protected function createQuery(): QueryBuilder
    {
        return $this
            ->connectionPool
            ->getQueryBuilderForTable('page')
            ->select('*')
            ->from('pages');
    }

    private function resolve(array $arguments): array
    {
        $uid = $arguments['uid'] ?? null;

        if (!$uid) {
            throw new GraphqlException('No uid provided in order to resolve a page');
        }

        $query = $this->createQuery();

        $query->where($query->expr()->eq('uid', $query->createNamedParameter($uid, \PDO::PARAM_INT)));

        foreach ($this->extenders as $extender) {
            $extender->extendQuery($query, $arguments);
        }

        try {
            $page = $query->executeQuery()->fetchAssociative();
        } catch (\Throwable $e) {
            throw new GraphqlException('Error on fetching page from database :'.$e->getMessage());
        }

        if (!$page) {
            throw GraphqlException::createClientSafe('Could not fetch page with id: '.$uid);
        }

        return $page;
    }
}
