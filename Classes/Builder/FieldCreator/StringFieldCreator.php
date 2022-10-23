<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;

class StringFieldCreator implements FieldCreatorInterface
{
    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(string $tableName, string $columnName): bool
    {
        return ColumnConfiguration::fromTableAndColumnOrNull($tableName, $columnName)?->isString() ?: false;
    }

    public function createField(string $tableName, string $columnName): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName(ColumnConfiguration::fromTableAndColumn($tableName, $columnName)->getGraphqlName())
            ->withResolver(fn ($record) => $record[$columnName])
        ;
    }
}