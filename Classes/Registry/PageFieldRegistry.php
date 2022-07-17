<?php

namespace RozbehSharahi\Graphql3\Registry;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;

class PageFieldRegistry
{
    protected array $fields = [];

    public function register(GraphqlNode $graphqlNode): self
    {
        $this->fields[$graphqlNode->getName()] = $graphqlNode;

        return $this;
    }

    public function getFields(): GraphqlNodeCollection
    {
        return new GraphqlNodeCollection($this->fields);
    }
}
