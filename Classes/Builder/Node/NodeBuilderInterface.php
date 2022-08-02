<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\Node;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;

interface NodeBuilderInterface
{
    public function build(): GraphqlNode;
}
