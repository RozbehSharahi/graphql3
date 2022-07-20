<?php

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\ObjectType;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Node\PageNode;

class QueryType extends ObjectType
{
    /**
     * @param iterable<QueryTypeExtenderInterface> $extenders
     */
    public function __construct(protected PageNode $pageNode, protected iterable $extenders)
    {
        parent::__construct([
            'name' => 'Query',
            'fields' => function () {
                $nodes = GraphqlNodeCollection::create([
                    $this->pageNode->getGraphqlNode(),
                    $this->pageNode->forSlug()->getGraphqlNode(),
                ]);

                foreach ($this->extenders as $extender) {
                    $nodes = $extender->extend($nodes);
                }

                return $nodes->toArray();
            },
        ]);
    }
}
