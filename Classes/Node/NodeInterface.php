<?php

namespace RozbehSharahi\Graphql3\Node;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;

interface NodeInterface
{
    public function getGraphqlNode(): GraphqlNode;
}
