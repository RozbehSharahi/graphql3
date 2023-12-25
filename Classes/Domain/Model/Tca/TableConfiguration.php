<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model\Tca;

use RozbehSharahi\Graphql3\Converter\CaseConverter;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TableConfiguration
{
    public const ERROR_INVALID_FLEX_FORM_COLUMNS = 'Invalid flex form columns configuration found for %s';

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
        return $this->getCaseConverter()->toCamelSingular($this->name);
    }

    public function getCamelPluralName(): string
    {
        return $this->getCaseConverter()->toCamelPlural($this->name);
    }

    public function getPascalSingularName(): string
    {
        return $this->getCaseConverter()->toPascalSingular($this->name);
    }

    public function getColumn(string $column): ColumnConfiguration
    {
        return ColumnConfiguration::create($this->name, $column);
    }

    public function hasColumn(string $column): bool
    {
        return !empty($this->configuration['columns'][$column]);
    }

    /**
     * @return array<int, ColumnConfiguration>
     */
    public function getColumns(): array
    {
        $columns = [];

        foreach ($this->configuration['columns'] ?? [] as $columnName => $configuration) {
            $columns[] = new ColumnConfiguration($this, $columnName, $configuration);
        }

        return $columns;
    }

    /**
     * @return array<int, ColumnConfiguration>
     */
    public function getFlexFormColumns(): array
    {
        $paths = $GLOBALS['TCA'][$this->getName()]['graphql3']['flexFormColumns'] ?? [];

        if (!is_array($paths)) {
            throw new InternalErrorException(sprintf(self::ERROR_INVALID_FLEX_FORM_COLUMNS, $this->name));
        }

        $columns = [];

        foreach ($paths as $path) {
            $columns[] = FlexFormFieldConfiguration::createFromString($this, $path)->getFlexColumn();
        }

        return $columns;
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

    public function getCreatedAt(): string
    {
        return $this->configuration['ctrl']['crdate'];
    }

    public function hasUpdatedAt(): bool
    {
        return !empty($this->configuration['ctrl']['tstamp']);
    }

    public function getUpdatedAt(): string
    {
        return $this->configuration['ctrl']['tstamp'];
    }

    public function hasLanguage(): bool
    {
        return !empty($this->configuration['ctrl']['languageField']);
    }

    public function hasSortingField(): bool
    {
        return !empty($this->configuration['ctrl']['sortby']);
    }

    public function getSortingField(): string
    {
        return $this->configuration['ctrl']['sortby'];
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
