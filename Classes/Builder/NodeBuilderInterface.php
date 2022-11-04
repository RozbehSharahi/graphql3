<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;

interface NodeBuilderInterface
{
    public function build(): GraphqlNode;

    public function buildArguments(): GraphqlArgumentCollection;

    public function buildType(): Type;

    public function buildResolver(): \Closure;
}
