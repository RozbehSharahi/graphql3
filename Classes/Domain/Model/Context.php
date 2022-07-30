<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

class Context
{
    public const TAG_PAGE_RESOLVE_BY_SLUG = 'page-resolve-by-slug';

    /**
     * @var array<string, true>
     */
    protected array $tagsMap;

    /**
     * @param string[] $tags
     */
    public function __construct(array $tags = [])
    {
        $this->tagsMap = $this->createTagMap($tags);
    }

    /**
     * @return array<int, string>
     */
    public function getTags(): array
    {
        return array_keys($this->tagsMap);
    }

    public function hasTag(string $tag): bool
    {
        return !empty($this->tagsMap[$tag]);
    }

    public function addTag(string $tag): self
    {
        $clone = clone $this;
        $clone->tagsMap[$tag] = true;

        return $clone;
    }

    public function removeTag(string $tag): self
    {
        $clone = clone $this;

        if ($this->tagsMap[$tag] ?? null) {
            unset($this->tagsMap[$tag]);
        }

        return $clone;
    }

    /**
     * @param string[] $tags
     */
    public function withTags(array $tags): self
    {
        $clone = clone $this;
        $clone->tagsMap = $this->createTagMap($tags);

        return $clone;
    }

    /**
     * @param string[] $tags
     *
     * @return array<string, true>
     */
    public function createTagMap(array $tags): array
    {
        $tagMap = [];

        foreach ($tags as $tag) {
            $tagMap[$tag] = true;
        }

        return $tagMap;
    }
}
