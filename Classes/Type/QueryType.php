<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\ObjectType;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Node\LanguageListNode;
use RozbehSharahi\Graphql3\Node\LanguageNode;
use RozbehSharahi\Graphql3\Node\PageListNode;
use RozbehSharahi\Graphql3\Node\PageNode;

class QueryType extends ObjectType
{
    /**
     * @param iterable<QueryTypeExtenderInterface> $extenders
     */
    public function __construct(
        protected PageNode $pageNestedNode,
        protected PageListNode $pageListNode,
        protected LanguageNode $languageNode,
        protected LanguageListNode $languageListNode,
        protected iterable $extenders
    ) {
        parent::__construct([
            'name' => 'Query',
            'fields' => function () {
                $nodes = GraphqlNodeCollection::create([
                    $this->pageNestedNode->getGraphqlNode(),
                    $this->pageNestedNode->forSlug()->getGraphqlNode(),
                    $this->pageListNode->getGraphqlNode(),
                    $this->languageNode->getGraphqlNode(),
                    $this->languageListNode->getGraphqlNode(),
                ]);

                foreach ($this->extenders as $extender) {
                    $nodes = $extender->extend($nodes);
                }

                return $nodes->toArray();
            },
        ]);
    }
}
