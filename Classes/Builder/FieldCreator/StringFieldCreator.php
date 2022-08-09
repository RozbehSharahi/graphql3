<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;

class StringFieldCreator extends AbstractFieldCreator implements FieldCreatorInterface
{
    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(string $tableName, string $columnName): bool
    {
        $configuration = $this->getFieldConfiguration($tableName, $columnName);

        return 'input' === $configuration['type'] || 'text' === $configuration['type'];
    }

    public function createField(string $tableName, string $columnName): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName($this->getName($tableName, $columnName))
            ->withResolver(fn ($record) => $record[$columnName])
        ;
    }
}
