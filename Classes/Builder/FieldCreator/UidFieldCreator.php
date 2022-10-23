<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;

class UidFieldCreator implements FieldCreatorInterface
{
    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(string $tableName, string $columnName): bool
    {
        return 'uid' === $columnName;
    }

    public function createField(string $tableName, string $columnName): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName($columnName)
            ->withType(Type::int())
            ->withResolver(fn (array $record) => $record[$columnName])
        ;
    }
}
