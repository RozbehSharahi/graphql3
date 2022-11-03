<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\FieldCreator;

use RozbehSharahi\Graphql3\Builder\RecordListTypeBuilder;
use RozbehSharahi\Graphql3\Builder\RecordTypeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

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

    public function supportsField(ColumnConfiguration $column): bool
    {
        return $column->isOneToMany();
    }

    public function createField(ColumnConfiguration $column): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName($column->getGraphqlName())
            ->withType($this->recordListTypeBuilder->for($column->getForeignTable())->build())
            ->withResolver(function (Record $record, array $args) use ($column) {
                return ListRequest::create($args)
                    ->withLanguageFromRecord($record)
                    ->withQueryModifier(
                        fn (QueryBuilder $q) => $q
                            ->andWhere($q->expr()->eq($column->getForeignField(), $record->get($column)))
                    )
                ;
            })
        ;
    }
}