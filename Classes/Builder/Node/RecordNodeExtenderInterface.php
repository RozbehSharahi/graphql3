<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\Node;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

interface RecordNodeExtenderInterface
{
    public function supportsTable(string $table): bool;

    public function extendArguments(string $table, GraphqlArgumentCollection $arguments): GraphqlArgumentCollection;

    /**
     * @param array<string, mixed> $arguments
     */
    public function extendQuery(string $table, QueryBuilder $query, array $arguments): QueryBuilder;
}
