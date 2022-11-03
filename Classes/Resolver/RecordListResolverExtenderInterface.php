<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Resolver;

use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

interface RecordListResolverExtenderInterface
{
    public function supports(TableConfiguration $table): bool;

    public function extend(TableConfiguration $table, ListRequest $request, QueryBuilder $query): QueryBuilder;
}
