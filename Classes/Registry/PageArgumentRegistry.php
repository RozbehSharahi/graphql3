<?php

namespace RozbehSharahi\Graphql3\Registry;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Setup\SetupInterface;

class PageArgumentRegistry implements SetupInterface
{
    /**
     * @var GraphqlArgument[]
     */
    protected array $items;

    public function register(GraphqlArgument $argument): self
    {
        $this->items[$argument->getName()] = $argument;

        return $this;
    }

    public function getArguments(): GraphqlArgumentCollection
    {
        return new GraphqlArgumentCollection($this->items);
    }

    public function setup(): void
    {
        $this
            ->register(GraphqlArgument::create('uid')->withType(Type::nonNull(Type::int())));
    }
}
