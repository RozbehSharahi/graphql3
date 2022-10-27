<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model\Tca;

use RozbehSharahi\Graphql3\Converter\CaseConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TableConfiguration
{
    public static function create(string $name): self
    {
        return GeneralUtility::makeInstance(self::class, $name, $GLOBALS['TCA'][$name]);
    }

    /**
     * @param array<string, array<string, mixed>> $configuration
     */
    public function __construct(protected string $name, protected array $configuration)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCamelSingularName(): string
    {
        return $this->getCaseConverter()->toCamelSingular($this->getName());
    }

    public function getPascalSingularName(): string
    {
        return $this->getCaseConverter()->toPascalSingular($this->name);
    }

    public function getColumn(string $column): ColumnConfiguration
    {
        return ColumnConfiguration::create($this->name, $column);
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

    public function getLanguageParent(): string
    {
        return $this->configuration['ctrl']['transOrigPointerField'];
    }

    public function getLanguage(): ?string
    {
        return $this->configuration['ctrl']['languageField'];
    }

    public function hasLanguageParent(): bool
    {
        return !empty($this->configuration['ctrl']['transOrigPointerField']);
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
        return !empty($this->configuration['ctrl']['enablecolumns']['fe_group']);
    }

    public function getAccessControl(): string
    {
        return $this->configuration['ctrl']['enablecolumns']['fe_group'];
    }

    /**
     * @return array<string, array<string, mixed>> $configuration
     */
    public function toArray(): array
    {
        return $this->configuration;
    }

    protected function getCaseConverter(): CaseConverter
    {
        return GeneralUtility::makeInstance(CaseConverter::class);
    }
}
