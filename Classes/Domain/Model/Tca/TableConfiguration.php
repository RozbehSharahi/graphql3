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

    public function getName(): string
    {
        return $this->table;
    }

    /**
     * @return array<string, array<string, mixed>> $configuration
     */
    public function toArray(): array
    {
        return $this->configuration;
    }

    public function getColumn(string $column): ColumnConfiguration
    {
        return ColumnConfiguration::fromConfiguration($this->table, $column, $this->configuration['columns'][$column]);
    }

    /**
     * @return array<int, string>
     */
    public function getColumns(): array
    {
        return array_keys($this->configuration['columns'] ?? []);
    }

    public function hasColumn(string $column): bool
    {
        return !empty($this->configuration['columns'][$column]);
    }

    public function getLanguageParentFieldName(): ?string
    {
        return $this->configuration['ctrl']['transOrigPointerField'] ?? null;
    }

    public function hasCreatedAt(): bool
    {
        return !empty($this->configuration['ctrl']['crdate']);
    }

    public function hasUpdatedAt(): bool
    {
        return !empty($this->configuration['ctrl']['tstamp']);
    }

    public function hasLanguage(): bool
    {
        return !empty($this->configuration['ctrl']['languageField']);
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
