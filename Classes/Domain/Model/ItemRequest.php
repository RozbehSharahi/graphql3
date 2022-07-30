<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

class ItemRequest
{
    public const DEFAULT_PUBLIC_REQUEST = true;
    public const PARAMETER_PUBLIC_REQUEST = 'publicRequest';

    public function __construct(protected array $arguments)
    {
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function get(string $argumentName, $default = null)
    {
        return $this->arguments[$argumentName] ?? $default;
    }

    public function isPublicRequest(): bool
    {
        return $this->arguments[self::PARAMETER_PUBLIC_REQUEST] ?? self::DEFAULT_PUBLIC_REQUEST;
    }
}
