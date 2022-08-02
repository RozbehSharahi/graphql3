<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;

class FloatFieldCreator extends AbstractFieldCreator implements FieldCreatorInterface
{
    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(string $tableName, string $columnName): bool
    {
        $configuration = $this->getFieldConfiguration($tableName, $columnName);

        return 'number' === $configuration['type'] && 'decimal' === $configuration['format'];
    }

    public function createField(string $tableName, string $columnName): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName($this->getName($tableName, $columnName))
            ->withType(Type::float())
            ->withResolver(fn (array $record) => $record[$columnName])
        ;
    }
}
