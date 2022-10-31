<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Resolver\LanguageListResolver;

class LanguageListNodeBuilder implements NodeBuilderInterface
{
    public function __construct(
        protected LanguageTypeBuilder $languageTypeBuilder,
        protected LanguageListResolver $languageListResolver
    ) {
    }

    public function build(): GraphqlNode
    {
        return GraphqlNode::create('languages')
            ->withType(Type::listOf($this->languageTypeBuilder->build()))
            ->withResolver($this->languageListResolver->getCallable())
        ;
    }
}
