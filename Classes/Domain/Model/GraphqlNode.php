<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use Closure;
use GraphQL\Type\Definition\Type;

class GraphqlNode
{
    public static function create(string $name): self
    {
        return new self($name, Type::string(), fn () => null, GraphqlArgumentCollection::create());
    }

    public function __construct(
        protected string $name,
        protected Type $type,
        protected Closure $resolver,
        protected GraphqlArgumentCollection $arguments
    ) {
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

    public function getType(): Type
    {
        return $this->type;
    }

    public function withType(Type $type): self
    {
        $clone = clone $this;
        $clone->type = $type;

        return $clone;
    }

    public function getArguments(): GraphqlArgumentCollection
    {
        return $this->arguments;
    }

    /**
     * @param GraphqlArgumentCollection|GraphqlArgument[] $arguments
     */
    public function withArguments(GraphqlArgumentCollection|array $arguments): self
    {
        if (is_array($arguments)) {
            $arguments = GraphqlArgumentCollection::create($arguments);
        }

        $clone = clone $this;
        $clone->arguments = $arguments;

        return $clone;
    }

    public function getResolver(): Closure
    {
        return $this->resolver;
    }

    public function withResolver(Closure $resolver): self
    {
        $clone = clone $this;
        $clone->resolver = $resolver;

        return $clone;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'args' => $this->arguments->toArray(),
            'resolve' => $this->resolver,
        ];
    }
}
