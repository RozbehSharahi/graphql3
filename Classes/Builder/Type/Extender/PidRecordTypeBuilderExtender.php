<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\Type\Extender;

use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilder;
use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilderExtenderInterface;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Domain\Model\ItemRequest;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use RozbehSharahi\Graphql3\Resolver\RecordResolver;

class PidRecordTypeBuilderExtender implements RecordTypeBuilderExtenderInterface
{
    public function __construct(
        protected RecordTypeBuilder $recordTypeBuilder,
        protected RecordResolver $recordResolver
    ) {
    }

    public function supportsTable(TableConfiguration $table): bool
    {
        return true;
    }

    public function extendNodes(
        TableConfiguration $table,
        GraphqlNodeCollection $nodes
    ): GraphqlNodeCollection {
        return $nodes->add(
            GraphqlNode::create()
                ->withName('parentPage')
                ->withType($this->recordTypeBuilder->for('pages')->build())
                ->withResolver(fn ($record) => $this
                    ->recordResolver->for('pages')->resolve(new ItemRequest(['uid' => $record['pid'] ?? null]))
                )
        );
    }
}
