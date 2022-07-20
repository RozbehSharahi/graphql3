<?php

namespace RozbehSharahi\Graphql3\Node;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

interface PageNodeExtenderInterface
{
    public function extendArguments(GraphqlArgumentCollection $arguments): GraphqlArgumentCollection;

    public function extendQuery(QueryBuilder $queryBuilder, array $arguments): QueryBuilder;
}
