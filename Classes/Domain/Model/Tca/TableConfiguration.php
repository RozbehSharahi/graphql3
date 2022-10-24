<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model\Tca;

class TableConfiguration
{
    public static function fromTableName(string $table): self
    {
        return new self($table, $GLOBALS['TCA'][$table]);
    }

    /**
     * @param array<string, array<string, mixed>> $configuration
     */
    public function __construct(protected string $table, protected array $configuration)
    {
    }

    public function getColumn(string $column): ColumnConfiguration
    {
        return ColumnConfiguration::fromConfiguration($this->table, $column, $this->configuration['columns'][$column]);
    }

    public function hasColumn(string $column): bool
    {
        return !empty($this->configuration['columns'][$column]);
    }

    public function getLanguageParentFieldName(): ?string
    {
        return $this->configuration['ctrl']['transOrigPointerField'] ?? null;
    }

    public function hasAccessControl(): bool
    {
        return (bool) $this->getAccessControlField();
    }

    public function getAccessControlField(): ?string
    {
        return $this->configuration['ctrl']['enablecolumns']['fe_group'] ?? null;
    }
}
