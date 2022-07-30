<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Type;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;

interface PageTypeExtenderInterface
{
    public function extendNodes(GraphqlNodeCollection $nodes): GraphqlNodeCollection;
}
