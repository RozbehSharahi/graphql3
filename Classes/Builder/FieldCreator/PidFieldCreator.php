<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ItemRequest;
use RozbehSharahi\Graphql3\Resolver\RecordResolver;

class PidFieldCreator implements FieldCreatorInterface
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

    public function supportsField(string $tableName, string $columnName): bool
    {
        return 'pid' === $columnName;
    }

    public function createField(string $tableName, string $columnName): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName('parentPage')
            ->withType($this->recordTypeBuilder->for('pages')->build())
            ->withResolver(fn ($record) => $this
                ->recordResolver->for('pages')->resolve(new ItemRequest(['uid' => $record[$columnName] ?? null]))
            )
        ;
    }
}
