<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Resolver;

use RozbehSharahi\Graphql3\Domain\Model\Context;
use RozbehSharahi\Graphql3\Domain\Model\ItemRequest;
use RozbehSharahi\Graphql3\Domain\Model\Page;
use RozbehSharahi\Graphql3\Exception\GraphqlException;
use RozbehSharahi\Graphql3\Node\PageNodeExtenderInterface;
use RozbehSharahi\Graphql3\Node\PageNodePostFetchFilterExtenderInterface;
use RozbehSharahi\Graphql3\Security\AccessChecker;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class PageResolver
{
    protected Context $context;

    /**
     * @param iterable<PageNodeExtenderInterface> $extenders
     */
    public function __construct(
        protected ConnectionPool $connectionPool,
        protected AccessChecker $accessChecker,
        protected iterable $extenders,
    ) {
        $this->context = new Context();
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function withContext(Context $context): self
    {
        $clone = clone $this;
        $clone->context = $context;

        return $clone;
    }

    public function getCallable(): callable
    {
        return function ($_, $arguments) {
            return $this->resolve(new ItemRequest($arguments));
        };
    }

    public function resolve(ItemRequest $request): ?array
    {
        $identifierName = 'uid';

        if ($this->context->hasTag(Context::TAG_PAGE_RESOLVE_BY_SLUG)) {
            $identifierName = 'slug';
        }

        $identifier = $request->get($identifierName);

        if (!$identifier) {
            throw new GraphqlException('No identifier provided in order to resolve a page');
        }

        $query = $this->createQuery();
        $query->where($query->expr()->eq($identifierName, $query->createNamedParameter($identifier)));
        $this->applyPublicRequestFilters($query, $request);

        foreach ($this->extenders as $extender) {
            if ($extender->supportsContext($this->context)) {
                $query = $extender->extendQuery($query, $request->getArguments());
            }
        }

        try {
            $pages = $query->executeQuery()->fetchAllAssociative();
        } catch (\Throwable $e) {
            throw new GraphqlException('Error on fetching page from database :'.$e->getMessage());
        }

        if (empty($pages)) {
            return null;
        }

        foreach ($this->extenders as $extender) {
            if (
                $extender instanceof PageNodePostFetchFilterExtenderInterface
                && $extender->supportsContext($this->context)
            ) {
                $pages = $extender->postFetchFilter($pages, $request->getArguments());
            }
        }

        $page = reset($pages);

        if (!$page) {
            throw GraphqlException::createClientSafe('Could not fetch page based on identifier: '.$identifier);
        }

        $this->accessChecker->assert(['VIEW'], new Page($page));

        return $page;
    }

    protected function createQuery(): QueryBuilder
    {
        return $this
            ->connectionPool
            ->getQueryBuilderForTable('page')
            ->select('*')
            ->from('pages')
        ;
    }

    protected function applyPublicRequestFilters(QueryBuilder $query, ItemRequest $request): self
    {
        if (!$request->isPublicRequest()) {
            return $this;
        }

        $query->andWhere('(fe_group="" OR fe_group = 0 OR fe_group IS NULL)');

        return $this;
    }
}
