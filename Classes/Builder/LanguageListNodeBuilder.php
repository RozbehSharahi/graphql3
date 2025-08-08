<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Resolver\LanguageListResolver;

class LanguageListNodeBuilder implements NodeBuilderInterface
{
    public function __construct(
        protected LanguageTypeBuilder $languageTypeBuilder,
        protected LanguageListResolver $languageListResolver,
    ) {
    }

    public function build(): GraphqlNode
    {
        return GraphqlNode::create('languages')
            ->withType($this->buildType())
            ->withArguments($this->buildArguments())
            ->withResolver($this->buildResolver())
        ;
    }

    public function buildArguments(): GraphqlArgumentCollection
    {
        return GraphqlArgumentCollection::create();
    }

    public function buildType(): Type
    {
        return Type::listOf($this->languageTypeBuilder->build());
    }

    public function buildResolver(): \Closure
    {
        return fn () => $this->languageListResolver->resolve();
    }
}
