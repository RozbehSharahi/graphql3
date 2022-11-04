<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\FieldCreator;

use RozbehSharahi\Graphql3\Builder\RecordListNodeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class OneToManyRelationFieldCreator implements FieldCreatorInterface
{
    public static function getPriority(): int
    {
        return 0;
    }

    public function __construct(protected RecordListNodeBuilder $recordListNodeBuilder)
    {
    }

    public function supportsField(ColumnConfiguration $column): bool
    {
        return $column->isOneToMany();
    }

    public function createField(ColumnConfiguration $column): GraphqlNode
    {
        $recordListNodeBuilder = $this->recordListNodeBuilder->for($column->getForeignTable());

        return GraphqlNode::create()
            ->withName($column->getGraphqlName())
            ->withType($recordListNodeBuilder->buildType())
            ->withArguments($recordListNodeBuilder->buildArguments()->remove('language'))
            ->withResolver(function (Record $record, array $args) use ($column) {
                $listRequest = ListRequest::create()
                    ->withArguments($args)
                    ->withQueryModifier(
                        fn (QueryBuilder $q) => $q->andWhere($q->expr()->eq(
                            $column->getForeignField(), $record->getUid()
                        ))
                    )
                ;

                if ($column->getForeignTable()->hasLanguage()) {
                    $listRequest = $listRequest->withLanguageFromRecord($record);
                }

                return $listRequest;
            })
        ;
    }
}
