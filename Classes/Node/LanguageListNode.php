<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Node;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Resolver\LanguageListResolver;
use RozbehSharahi\Graphql3\Type\LanguageType;

class LanguageListNode implements NodeInterface
{
    public function __construct(
        protected LanguageType $languageType,
        protected LanguageListResolver $languageListResolver
    ) {
    }

    public function getGraphqlNode(): GraphqlNode
    {
        return GraphqlNode::create('languages')
            ->withType(Type::listOf($this->languageType))
            ->withResolver($this->languageListResolver->getCallable())
        ;
    }
}
