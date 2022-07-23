<?php

namespace RozbehSharahi\Graphql3\Domain\Model;

class ListRequest
{
    public const DEFAULT_PAGE = 1;
    public const DEFAULT_PAGE_SIZE = 10;
    public const PARAMETER_PAGE = 'page';
    public const PARAMETER_PAGE_SIZE = 'pageSize';

    public function __construct(protected array $arguments)
    {
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getPage(): int
    {
        return $this->arguments[self::PARAMETER_PAGE] ?? self::DEFAULT_PAGE;
    }

    public function getPageSize(): int
    {
        return $this->arguments[self::PARAMETER_PAGE_SIZE] ?? self::DEFAULT_PAGE_SIZE;
    }
}
