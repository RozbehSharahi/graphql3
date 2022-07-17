<?php

namespace RozbehSharahi\Graphql3\Registry;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;

class PageArgumentRegistry
{
    /**
     * @var GraphqlArgument[]
     */
    protected array $items = [];

    public function register(GraphqlArgument $argument): self
    {
        $this->items[$argument->getName()] = $argument;

        return $this;
    }

    public function getArguments(): GraphqlArgumentCollection
    {
        return new GraphqlArgumentCollection($this->items);
    }
}
