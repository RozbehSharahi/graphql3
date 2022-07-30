<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use GraphQL\Type\Definition\Type;

class GraphqlArgument
{
    public const DEFAULT_VALUE_UNDEFINED = '#### THIS IS A KEY FOR AN UNDEFINED DEFAULT VALUE ####';

    public static function create(string $name): self
    {
        return new self($name, Type::string(), self::DEFAULT_VALUE_UNDEFINED);
    }

    public function __construct(
        protected string $name,
        protected Type $type,
        protected mixed $defaultValue
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

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function withDefaultValue(mixed $defaultValue): self
    {
        $clone = clone $this;
        $clone->defaultValue = $defaultValue;

        return $clone;
    }

    public function toArray(): array
    {
        $array = [
            'name' => $this->name,
            'type' => $this->type,
        ];

        if (self::DEFAULT_VALUE_UNDEFINED !== $this->defaultValue) {
            $array['defaultValue'] = $this->defaultValue;
        }

        return $array;
    }
}
