<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\FieldCreator;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;

class FloatFieldCreator implements FieldCreatorInterface
{
    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(ColumnConfiguration $column): bool
    {
        return $column->isFloat();
    }

    public function createField(ColumnConfiguration $column): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName($column->getGraphqlName())
            ->withType(Type::float())
            ->withResolver(fn (Record $record) => $record->get($column))
        ;
    }
}
