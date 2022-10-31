<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\FieldCreator;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;

class DateTimeFieldCreator implements FieldCreatorInterface
{
    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(ColumnConfiguration $column): bool
    {
        return $column->isDateTime();
    }

    public function createField(ColumnConfiguration $column): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName($column->getGraphqlName())
            ->withArguments(GraphqlArgumentCollection::create([GraphqlArgument::createDateTimeFormat()]))
            ->withResolver(fn (Record $record, $args) => date($args['format'], $record->get($column)))
        ;
    }
}
