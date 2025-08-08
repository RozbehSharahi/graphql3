<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder;

use GraphQL\Type\Definition\Type;
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
        protected LanguageResolver $languageResolver,
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
        return GraphqlNode::create()
            ->withName($this->name)
            ->withType($this->buildType())
            ->withArguments($this->buildArguments())
            ->withResolver($this->buildResolver())
        ;
    }

    public function buildArguments(): GraphqlArgumentCollection
    {
        return GraphqlArgumentCollection::create([
            GraphqlArgument::create('id')->withType(Type::nonNull(Type::int())),
        ]);
    }

    public function buildType(): Type
    {
        return $this->languageTypeBuilder->build();
    }

    public function buildResolver(): \Closure
    {
        return fn ($_, $args) => $this->languageResolver->resolve(ItemRequest::create($args));
    }
}
