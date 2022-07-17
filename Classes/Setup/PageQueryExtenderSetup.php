<?php

namespace RozbehSharahi\Graphql3\Setup;

use RozbehSharahi\Graphql3\Domain\Model\QueryExtender;
use RozbehSharahi\Graphql3\Registry\PageQueryExtenderRegistry;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class PageQueryExtenderSetup implements SetupInterface
{
    public function __construct(protected PageQueryExtenderRegistry $pageQueryExtenderRegistry)
    {
    }

    public function setup(): void
    {
        $this->pageQueryExtenderRegistry
            ->register(
                QueryExtender::create('uid-extender')
                    ->withClosure(
                        static fn (QueryBuilder $query, array $arguments = []) => $query
                            ->andWhere($query->expr()->eq('uid', $query->createNamedParameter($arguments['uid'])))
                    )
            );
    }
}
