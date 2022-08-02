<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\ObjectType;
use RozbehSharahi\Graphql3\Builder\Node\LanguageListNodeBuilder;
use RozbehSharahi\Graphql3\Builder\Node\LanguageNodeBuilder;
use RozbehSharahi\Graphql3\Builder\Node\RecordListNodeBuilder;
use RozbehSharahi\Graphql3\Builder\Node\RecordNodeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;

class QueryType extends ObjectType
{
    /**
     * @param iterable<QueryTypeExtenderInterface> $extenders
     */
    public function __construct(
        protected RecordNodeBuilder $recordNodeBuilder,
        protected RecordListNodeBuilder $recordListNodeBuilder,
        protected LanguageNodeBuilder $languageNodeBuilder,
        protected LanguageListNodeBuilder $languageListNodeBuilder,
        protected iterable $extenders
    ) {
        parent::__construct([
            'name' => 'Query',
            'fields' => function () {
                $nodes = GraphqlNodeCollection::create([
                    $this->recordNodeBuilder->for('pages')->build(),
                    $this->recordListNodeBuilder->for('pages')->build(),
                    $this->languageNodeBuilder->build(),
                    $this->languageListNodeBuilder->build(),
                ]);

                foreach ($this->extenders as $extender) {
                    $nodes = $extender->extend($nodes);
                }

                return $nodes->toArray();
            },
        ]);
    }
}
