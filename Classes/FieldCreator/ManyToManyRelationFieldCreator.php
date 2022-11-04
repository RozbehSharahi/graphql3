<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\FieldCreator;

use RozbehSharahi\Graphql3\Builder\RecordListTypeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class ManyToManyRelationFieldCreator implements FieldCreatorInterface
{
    public static function getPriority(): int
    {
        return 0;
    }

    public function __construct(protected RecordListTypeBuilder $recordListTypeBuilder)
    {
    }

    public function supportsField(ColumnConfiguration $column): bool
    {
        return $column->isManyToMany();
    }

    public function createField(ColumnConfiguration $column): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName($column->getGraphqlName())
            ->withType($this->recordListTypeBuilder->for($column->getForeignTable())->build())
            ->withResolver(function (Record $record, array $args) use ($column) {
                $relationTable = $column->getRelationTable();
                $localField = 'uid_local';
                $foreignField = 'uid_foreign';

                if ($column->hasManyToManyOpposite()) {
                    $localField = 'uid_foreign';
                    $foreignField = 'uid_local';
                }

                return ListRequest::create()
                    ->withArguments($args)
                    ->withQueryModifier(
                        fn (QueryBuilder $q) => $q->andWhere(
                            "uid in (
                                SELECT {$foreignField} 
                                FROM {$relationTable} 
                                WHERE {$localField}={$record->getUid()}
                            )"
                        )
                    )
                ;
            })
        ;
    }
}
