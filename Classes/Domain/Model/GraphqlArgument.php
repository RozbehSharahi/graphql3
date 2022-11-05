<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use GraphQL\Type\Definition\Type;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GraphqlArgument
{
    public const DEFAULT_VALUE_UNDEFINED = '#### THIS IS A KEY FOR AN UNDEFINED DEFAULT VALUE ####';

    public static function create(string $name = 'unnamed'): self
    {
        return GeneralUtility::makeInstance(self::class, $name, Type::string(), self::DEFAULT_VALUE_UNDEFINED);
    }

    public static function createDateTimeFormat(): self
    {
        return self::create('format')->withType(Type::nonNull(Type::string()))->withDefaultValue('Y-m-d H:i');
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

    /**
     * @return array<string, mixed>
     */
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
