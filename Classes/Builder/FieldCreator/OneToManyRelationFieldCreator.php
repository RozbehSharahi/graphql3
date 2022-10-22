<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use RozbehSharahi\Graphql3\Builder\Type\RecordListTypeBuilder;
use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ListRequest;

class OneToManyRelationFieldCreator extends AbstractFieldCreator implements FieldCreatorInterface
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
        $configuration = $this->getFieldConfiguration($tableName, $columnName);

        return 'inline' === $configuration['type'] && !empty($configuration['foreign_table']);
    }

    public function createField(string $tableName, string $columnName): GraphqlNode
    {
        $configuration = $this->getFieldConfiguration($tableName, $columnName);

        return GraphqlNode::create()
            ->withName($this->getName($tableName, $columnName))
            ->withType($this->recordListTypeBuilder->for($configuration['foreign_table'])->build())
            ->withResolver(fn () => new ListRequest([]))
        ;
    }
}
