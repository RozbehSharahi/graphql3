<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\ObjectType;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;

class MutationType extends ObjectType
{
    /**
     * @param iterable<MutationTypeExtenderInterface> $extenders
     */
    public function __construct(protected iterable $extenders)
    {
        parent::__construct([
            'name' => 'Mutation',
            'fields' => function () {
                $nodes = GraphqlNodeCollection::create()
                    ->add(GraphqlNode::create('ping')->withResolver(fn () => 'pong'))
                ;

                foreach ($this->extenders as $extender) {
                    $nodes = $extender->extend($nodes);
                }

                return $nodes->toArray();
            },
        ]);
    }
}
