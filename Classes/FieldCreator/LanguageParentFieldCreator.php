<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\FieldCreator;

use RozbehSharahi\Graphql3\Builder\RecordNodeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ItemRequest;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;
use RozbehSharahi\Graphql3\Resolver\RecordResolver;

class LanguageParentFieldCreator implements FieldCreatorInterface
{
    public function __construct(
        protected RecordNodeBuilder $recordNodeBuilder,
        protected RecordResolver $recordResolver
    ) {
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(ColumnConfiguration $column): bool
    {
        return $column->isLanguageParent();
    }

    public function createField(ColumnConfiguration $column): GraphqlNode
    {
        $recordNodeBuilder = $this->recordNodeBuilder->for($column->getTable());

        return GraphqlNode::create()
            ->withName('languageParent')
            ->withType($recordNodeBuilder->buildType())
            ->withArguments($recordNodeBuilder->buildArguments()->remove('uid'))
            ->withResolver(fn (Record $record) => $this
                ->recordResolver
                ->for($column->getTable())
                ->resolve(ItemRequest::create(['uid' => $record->get($column)]))
            )
        ;
    }
}
