<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\ObjectType;
use RozbehSharahi\Graphql3\Builder\LanguageListNodeBuilder;
use RozbehSharahi\Graphql3\Builder\LanguageNodeBuilder;
use RozbehSharahi\Graphql3\Builder\RecordListNodeBuilder;
use RozbehSharahi\Graphql3\Builder\RecordNodeBuilder;
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
                    $this->recordNodeBuilder->for('tt_content')->build()->withName('content'),
                    $this->recordListNodeBuilder->for('tt_content')->build()->withName('contents'),
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
