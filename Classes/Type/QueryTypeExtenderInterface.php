<?php

namespace RozbehSharahi\Graphql3\Type;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;

interface QueryTypeExtenderInterface
{
    public function extend(GraphqlNodeCollection $nodes): GraphqlNodeCollection;
}
