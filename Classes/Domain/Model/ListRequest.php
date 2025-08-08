<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class ListRequest
{
    public const DEFAULT_PAGE = 1;
    public const DEFAULT_PAGE_SIZE = 10;
    public const DEFAULT_ORDER_BY = [['field' => 'uid', 'direction' => 'ASC']];
    public const DEFAULT_FILTERS = [];
    public const DEFAULT_PUBLIC_REQUEST = true;
    public const DEFAULT_LANGUAGE = null;
    public const PARAMETER_PAGE = 'page';
    public const PARAMETER_PAGE_SIZE = 'pageSize';
    public const PARAMETER_ORDER_BY = 'orderBy';
    public const PARAMETER_FILTERS = 'filters';
    public const PARAMETER_PUBLIC_REQUEST = 'publicRequest';
    public const PARAMETER_LANGUAGE = 'language';

    protected \Closure $queryModifier;

    /**
     * @param array<string, mixed> $arguments
     */
    public static function create(array $arguments = [], ?\Closure $queryModifier = null): self
    {
        return GeneralUtility::makeInstance(self::class, $arguments, $queryModifier);
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public function __construct(protected array $arguments = [], ?\Closure $queryModifier = null)
    {
        $this->queryModifier = $queryModifier ?: static fn ($v) => $v;
    }

    /**
     * @return array<string, mixed>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param array<string,mixed> $arguments
     */
    public function withArguments(array $arguments): self
    {
        $clone = clone $this;
        $clone->arguments = $arguments;

        return $clone;
    }

    public function hasArgument(string $name): bool
    {
        return array_key_exists($name, $this->arguments);
    }

    public function getArgument(string $name): mixed
    {
        return $this->arguments[$name];
    }

    public function getQueryModifier(): \Closure
    {
        return $this->queryModifier;
    }

    public function withQueryModifier(\Closure $queryModifier): self
    {
        $clone = clone $this;
        $clone->queryModifier = $queryModifier;

        return $clone;
    }

    public function getPage(): int
    {
        return $this->arguments[self::PARAMETER_PAGE] ?? self::DEFAULT_PAGE;
    }

    public function getPageSize(): int
    {
        return $this->arguments[self::PARAMETER_PAGE_SIZE] ?? self::DEFAULT_PAGE_SIZE;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getOrderBy(): array
    {
        return $this->arguments[self::PARAMETER_ORDER_BY] ?? self::DEFAULT_ORDER_BY;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFilters(): array
    {
        return $this->arguments[self::PARAMETER_FILTERS] ?? self::DEFAULT_FILTERS;
    }

    public function getLanguage(): ?string
    {
        return $this->arguments[self::PARAMETER_LANGUAGE] ?? self::DEFAULT_LANGUAGE;
    }

    public function withLanguageFromRecord(Record $record): self
    {
        if (!$record->getTable()->hasLanguage()) {
            return $this;
        }

        $clone = clone $this;
        $clone->arguments[self::PARAMETER_LANGUAGE] = $record->getLanguage()->getLocale()->getLanguageCode();

        return $clone;
    }

    public function isPublicRequest(): bool
    {
        return $this->arguments[self::PARAMETER_PUBLIC_REQUEST] ?? self::DEFAULT_PUBLIC_REQUEST;
    }
}
