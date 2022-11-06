<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\FlexFormFieldCreator;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;

class IntegerFlexFormFieldCreator implements FlexFormFieldCreatorInterface
{
    public function supportsField(ColumnConfiguration $column): bool
    {
        return $column->isInt();
    }

    public function createField(ColumnConfiguration $column): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName($column->getGraphqlName())
            ->withType(Type::float())
            ->withResolver(fn (Record $record) => $record->getFlexFormValue($column->getFlexFormPointer()))
        ;
    }
}
