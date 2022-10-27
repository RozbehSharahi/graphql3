<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use RozbehSharahi\Graphql3\Builder\Type\RecordListTypeBuilder;
use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilder;
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
            ->withResolver(function (array $row, array $args) use ($column) {
                $foreignField = $column->getForeignField();
                $record = Record::create($column->getTable(), $row);

                return ListRequest::create($args)
                    ->withLanguageFromRecord($record)
                    ->withQueryModifier(
                        fn (QueryBuilder $q) => $q->andWhere($q->expr()->eq($foreignField, $row['uid']))
                    )
                ;
            })
        ;
    }
}
