<?php

namespace RozbehSharahi\Graphql3\Domain\Model;

use Closure;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class QueryExtender
{
    public const DEFAULT_POSITION = 100;

    public static function create(string $name, Closure $closure = null, int $position = self::DEFAULT_POSITION): self
    {
        return new self($name, $closure ?: static fn (QueryBuilder $query) => $query, $position);
    }

    public function __construct(protected string $name, protected Closure $closure, protected int $position)
    {
    }

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

    public function getClosure(): Closure
    {
        return $this->closure;
    }

    public function withClosure(Closure $closure): self
    {
        $clone = clone $this;
        $clone->closure = $closure;

        return $clone;
    }
}
