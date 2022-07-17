<?php

namespace RozbehSharahi\Graphql3\Builder;

use RozbehSharahi\Graphql3\Exception\GraphqlException;
use RozbehSharahi\Graphql3\Registry\PageQueryExtenderRegistry;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class RegistryBasedPageQueryBuilder
{
    protected array $arguments;

    public function __construct(
        protected ConnectionPool $connectionPool,
        protected PageQueryExtenderRegistry $pageQueryExtenderRegistry
    ) {
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function withArguments(array $arguments): self
    {
        $clone = clone $this;
        $clone->arguments = $arguments;

        return $clone;
    }

    public function build(): QueryBuilder
    {
        if (!isset($this->arguments)) {
            throw new GraphqlException('No arguments provided, although page uid is mandatory');
        }

        $query = $this->createQuery();

        foreach ($this->pageQueryExtenderRegistry->getExtenders() as $extender) {
            $query = $extender->getClosure()($query, $this->arguments);

            if (!$query instanceof QueryBuilder) {
                throw new GraphqlException('One of your page query extenders did not return a query');
            }
        }

        return $query;
    }

    public function getPage(): array
    {
        $uid = $this->arguments['uid'];
        $query = $this->build();

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

    protected function createQuery(): QueryBuilder
    {
        return $this
            ->connectionPool
            ->getQueryBuilderForTable('page')
            ->select('*')
            ->from('pages');
    }
}
