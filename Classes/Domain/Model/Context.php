<?php

namespace RozbehSharahi\Graphql3\Domain\Model;

class Context
{
    public const TAG_PAGE_RESOLVE_BY_SLUG = 'page-resolve-by-slug';

    public function __construct(protected array $tags = [])
    {
        $this->tags = $this->createTagMap($this->tags);
    }

    public function getTags(): array
    {
        return array_keys($this->tags);
    }

    public function hasTag(string $tag): bool
    {
        return !empty($this->tags[$tag]);
    }

    public function addTag(string $tag): self
    {
        $clone = clone $this;
        $clone->tags[$tag] = true;

        return $clone;
    }

    public function removeTag(string $tag): self
    {
        $clone = clone $this;

        if ($this->tags[$tag] ?? null) {
            unset($this->tags[$tag]);
        }

        return $clone;
    }

    public function withTags(array $tags): self
    {
        $clone = clone $this;
        $clone->tags = $this->createTagMap($tags);

        return $clone;
    }

    public function createTagMap($tags): array
    {
        $tagMap = [];

        foreach ($tags as $tag) {
            $tagMap[$tag] = true;
        }

        return $tagMap;
    }
}
