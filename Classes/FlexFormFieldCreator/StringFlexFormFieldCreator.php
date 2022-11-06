<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\FlexFormFieldCreator;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;

class StringFlexFormFieldCreator implements FlexFormFieldCreatorInterface
{
    public function supportsField(ColumnConfiguration $column): bool
    {
        return $column->isString();
    }

    public function createField(ColumnConfiguration $column): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName($column->getGraphqlName())
            ->withType(Type::string())
            ->withResolver(fn (Record $record) => $record->getFlexFormValue($column->getFlexFormPointer()))
        ;
    }
}
