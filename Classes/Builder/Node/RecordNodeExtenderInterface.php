<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\Node;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

interface RecordNodeExtenderInterface
{
    public function supportsTable(TableConfiguration $table): bool;

    public function extendArguments(
        TableConfiguration $table,
        GraphqlArgumentCollection $arguments
    ): GraphqlArgumentCollection;

    /**
     * @param array<string, mixed> $arguments
     */
    public function extendQuery(TableConfiguration $table, QueryBuilder $query, array $arguments): QueryBuilder;
}
