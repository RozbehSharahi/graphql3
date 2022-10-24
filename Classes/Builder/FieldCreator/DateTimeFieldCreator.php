<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;

class DateTimeFieldCreator implements FieldCreatorInterface
{
    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(string $tableName, string $columnName): bool
    {
        return ColumnConfiguration::fromTableAndColumnOrNull($tableName, $columnName)?->isDateTime() ?: false;
    }

    public function createField(string $tableName, string $columnName): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName(ColumnConfiguration::fromTableAndColumn($tableName, $columnName)->getGraphqlName())
            ->withArguments(GraphqlArgumentCollection::create([
                GraphqlArgument::create('format')
                    ->withType(Type::nonNull(Type::string()))
                    ->withDefaultValue('Y-m-d H:i'),
            ]))
            ->withResolver(fn ($record, $args) => date($args['format'], $record[$columnName]))
        ;
    }
}
