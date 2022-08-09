<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\Node;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Builder\Type\LanguageTypeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ItemRequest;
use RozbehSharahi\Graphql3\Resolver\LanguageResolver;

class LanguageNodeBuilder implements NodeBuilderInterface
{
    protected string $name = 'language';

    public function __construct(
        protected LanguageTypeBuilder $languageTypeBuilder,
        protected LanguageResolver $languageResolver
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function withName(string $name): self
    {
        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }

    public function build(): GraphqlNode
    {
        return GraphqlNode::create($this->name)
            ->withType($this->languageTypeBuilder->build())
            ->withArguments(
                GraphqlArgumentCollection::create([
                    GraphqlArgument::create('id')->withType(Type::nonNull(Type::int())),
                ])
            )
            ->withResolver(fn ($_, $args) => $this->languageResolver->resolve(new ItemRequest($args)))
        ;
    }
}
