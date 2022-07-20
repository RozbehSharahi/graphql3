<?php

namespace RozbehSharahi\Graphql3\Resolver;

use RozbehSharahi\Graphql3\Domain\Model\PageResolverContext;
use RozbehSharahi\Graphql3\Exception\GraphqlException;
use RozbehSharahi\Graphql3\Node\PageNodeExtenderInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class PageResolver
{
    /**
     * @param iterable<PageNodeExtenderInterface> $extenders
     */
    public function __construct(
        protected ConnectionPool $connectionPool,
        protected iterable $extenders,
        protected ?PageResolverContext $context = null
    ) {
        $this->context ??= (new PageResolverContext())->withTargetIdentifier('uid');
    }

    public function getContext(): PageResolverContext
    {
        return $this->context;
    }

    public function withContext(PageResolverContext $context): self
    {
        $clone = clone $this;
        $clone->context = $context;

        return $clone;
    }

    public function getCallable(): callable
    {
        return function ($_, $arguments) {
            return $this->resolve($arguments);
        };
    }

    protected function resolve(array $arguments): array
    {
        $identifier = $arguments[$this->context->getTargetIdentifier()] ?? null;

        if (!$identifier) {
            throw new GraphqlException('No identifier provided in order to resolve a page');
        }

        $query = $this->createQuery();
        $query->where($query->expr()->eq(
            $this->context->getTargetIdentifier(), $query->createNamedParameter($identifier)
        ));

        foreach ($this->extenders as $extender) {
            if ($extender->supportsContext($this->context)) {
                $query = $extender->extendQuery($query, $arguments);
            }
        }

        try {
            $page = $query->executeQuery()->fetchAssociative();
        } catch (\Throwable $e) {
            throw new GraphqlException('Error on fetching page from database :'.$e->getMessage());
        }

        if (!$page) {
            throw GraphqlException::createClientSafe('Could not fetch page based on identifier: '.$identifier);
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
