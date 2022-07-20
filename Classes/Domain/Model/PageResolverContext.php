<?php

namespace RozbehSharahi\Graphql3\Domain\Model;

class PageResolverContext
{
    public function __construct(protected string $targetIdentifier = 'uid')
    {
    }

    public function getTargetIdentifier(): string
    {
        return $this->targetIdentifier;
    }

    public function withTargetIdentifier(string $key): self
    {
        $clone = clone $this;
        $clone->targetIdentifier = $key;

        return $clone;
    }
}
