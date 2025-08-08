<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\FieldCreator;

use RozbehSharahi\Graphql3\Builder\RecordNodeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ItemRequest;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;
use RozbehSharahi\Graphql3\Resolver\RecordResolver;

class ManyToOneRelationFieldCreator implements FieldCreatorInterface
{
    public static function getPriority(): int
    {
        return 0;
    }

    public function __construct(
        protected RecordNodeBuilder $recordNodeBuilder,
        protected RecordResolver $recordResolver,
    ) {
    }

    public function supportsField(ColumnConfiguration $column): bool
    {
        return $column->isManyToOne() && !$column->isLanguageParent();
    }

    public function createField(ColumnConfiguration $column): GraphqlNode
    {
        $recordNodeBuilder = $this->recordNodeBuilder->for($column->getForeignTable());

        return GraphqlNode::create()
            ->withName($column->getGraphqlName())
            ->withType($recordNodeBuilder->buildType())
            ->withArguments($recordNodeBuilder->buildArguments()->remove('uid'))
            ->withResolver(fn (Record $record) => $this
                ->recordResolver
                ->for($column->getForeignTable())
                ->resolve(ItemRequest::create(['uid' => $record->get($column)]))
            )
        ;
    }
}
