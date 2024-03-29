<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class ItemRequest
{
    public const DEFAULT_PUBLIC_REQUEST = true;
    public const PARAMETER_PUBLIC_REQUEST = 'publicRequest';

    /**
     * @param array<string, mixed> $arguments
     */
    public static function create(array $arguments): self
    {
        return GeneralUtility::makeInstance(self::class, $arguments);
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public function __construct(protected array $arguments)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function get(string $argumentName, mixed $default = null): mixed
    {
        return $this->arguments[$argumentName] ?? $default;
    }

    public function isPublicRequest(): bool
    {
        return $this->arguments[self::PARAMETER_PUBLIC_REQUEST] ?? self::DEFAULT_PUBLIC_REQUEST;
    }
}
