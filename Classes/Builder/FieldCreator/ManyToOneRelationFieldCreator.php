<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ItemRequest;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;
use RozbehSharahi\Graphql3\Resolver\RecordResolver;

class ManyToOneRelationFieldCreator implements FieldCreatorInterface
{
    public function __construct(
        protected RecordTypeBuilder $recordTypeBuilder,
        protected RecordResolver $recordResolver
    ) {
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(ColumnConfiguration $column): bool
    {
        return $column->isManyToOne() && !$column->isLanguageParent();
    }

    public function createField(ColumnConfiguration $column): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName($column->getGraphqlName())
            ->withType($this->recordTypeBuilder->for($column->getForeignTable())->build())
            ->withResolver(fn ($record) => $this
                ->recordResolver
                ->for($column->getForeignTable())
                ->resolve(ItemRequest::create(['uid' => $record[$column->getName()] ?? null]))
            )
        ;
    }
}
