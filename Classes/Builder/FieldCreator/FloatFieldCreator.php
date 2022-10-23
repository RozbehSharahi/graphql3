<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;

class FloatFieldCreator implements FieldCreatorInterface
{
    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(string $tableName, string $columnName): bool
    {
        return ColumnConfiguration::fromTableAndColumnOrNull($tableName, $columnName)?->isFloat() ?: false;
    }

    public function createField(string $tableName, string $columnName): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName(ColumnConfiguration::fromTableAndColumn($tableName, $columnName)->getGraphqlName())
            ->withType(Type::float())
            ->withResolver(fn (array $record) => $record[$columnName])
        ;
    }
}
