<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;

class BoolFieldCreator implements FieldCreatorInterface
{
    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(string $tableName, string $columnName): bool
    {
        return
            'deleted' === $columnName ||
            ColumnConfiguration::fromTableAndColumnOrNull($tableName, $columnName)?->isBool()
        ;
    }

    public function createField(string $tableName, string $columnName): GraphqlNode
    {
        if ('deleted' === $columnName) {
            return GraphqlNode::create()
                ->withName($columnName)
                ->withType(Type::nonNull(Type::boolean()))
                ->withResolver(fn (array $record) => !empty($record[$columnName]))
            ;
        }

        $config = ColumnConfiguration::fromTableAndColumn($tableName, $columnName);

        return GraphqlNode::create()
            ->withName($config->getGraphqlName())
            ->withType(Type::nonNull(Type::boolean()))
            ->withResolver(fn (array $record) => !empty($record[$columnName]))
        ;
    }
}
