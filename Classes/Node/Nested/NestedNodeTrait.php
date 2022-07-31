<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Node\Nested;

trait NestedNodeTrait
{
    protected string $name = 'unnamed';

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
}
