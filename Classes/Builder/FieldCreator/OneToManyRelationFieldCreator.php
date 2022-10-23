<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use RozbehSharahi\Graphql3\Builder\Type\RecordListTypeBuilder;
use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;

class OneToManyRelationFieldCreator implements FieldCreatorInterface
{
    public function __construct(
        protected RecordTypeBuilder $recordTypeBuilder,
        protected RecordListTypeBuilder $recordListTypeBuilder,
    ) {
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(string $tableName, string $columnName): bool
    {
        return ColumnConfiguration::fromTableAndColumnOrNull($tableName, $columnName)?->isOneToMany() ?: false;
    }

    public function createField(string $tableName, string $columnName): GraphqlNode
    {
        $config = ColumnConfiguration::fromTableAndColumn($tableName, $columnName);

        return GraphqlNode::create()
            ->withName($config->getGraphqlName())
            ->withType($this->recordListTypeBuilder->for($config->getForeignTable())->build())
            ->withResolver(fn () => new ListRequest([]))
        ;
    }
}