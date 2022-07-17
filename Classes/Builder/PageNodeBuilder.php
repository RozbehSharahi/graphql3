<?php

namespace RozbehSharahi\Graphql3\Builder;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Registry\PageArgumentRegistry;
use RozbehSharahi\Graphql3\Registry\TypeRegistry;
use RozbehSharahi\Graphql3\Type\RegistryBasedPageType;

class PageNodeBuilder
{
    protected string $name = 'page';

    public function __construct(
        protected TypeRegistry $typeRegistry,
        protected PageArgumentRegistry $pageArgumentRegistry,
        protected RegistryBasedPageQueryBuilder $pageQueryBuilder
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
            ->withType($this->typeRegistry->get(RegistryBasedPageType::class))
            ->withArguments($this->pageArgumentRegistry->getArguments())
            ->withResolver(fn ($_, $args) => $this->pageQueryBuilder->withArguments($args)->getPage());
    }
}
