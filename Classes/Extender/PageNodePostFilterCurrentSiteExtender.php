<?php

namespace RozbehSharahi\Graphql3\Extender;

use RozbehSharahi\Graphql3\Domain\Model\Context;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Node\PageNodeExtenderInterface;
use RozbehSharahi\Graphql3\Node\PageNodePostFetchFilterExtenderInterface;
use RozbehSharahi\Graphql3\Site\CurrentSite;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;

class PageNodePostFilterCurrentSiteExtender implements PageNodeExtenderInterface, PageNodePostFetchFilterExtenderInterface
{
    public function __construct(
        protected CurrentSite $currentSite,
        protected SiteFinder $siteFinder
    ) {
    }

    public function supportsContext(Context $context): bool
    {
        return $context->hasTag(Context::TAG_PAGE_RESOLVE_BY_SLUG);
    }

    public function extendArguments(GraphqlArgumentCollection $arguments): GraphqlArgumentCollection
    {
        return $arguments;
    }

    public function extendQuery(QueryBuilder $query, array $arguments): QueryBuilder
    {
        return $query;
    }

    public function postFetchFilter(array $pages, array $arguments): array
    {
        $currentSite = $this->currentSite->get();

        return array_filter(
            $pages,
            function ($v) use ($currentSite) {
                $pageSite = $this->getSiteByPageId($v['uid']);

                return $pageSite?->getIdentifier() === $currentSite->getIdentifier();
            }
        );
    }

    protected function getSiteByPageId(int $uid): ?Site
    {
        try {
            return $this->siteFinder->getSiteByPageId($uid);
        } catch (SiteNotFoundException) {
            return null;
        }
    }
}
