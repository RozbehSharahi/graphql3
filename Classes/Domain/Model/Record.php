<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use RozbehSharahi\Graphql3\Exception\GraphqlException;
use RozbehSharahi\Graphql3\Site\CurrentSite;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Page object.
 *
 * This class is mainly used to pass a page array to voters.
 *
 * By this it is easier to determine the type of the record on voters and it also provides some helpful getters
 * in order to do permission checking.
 *
 * However, you can also retrieve the page array by Page::getData().
 */
class Record
{
    protected TableConfiguration $tableConfiguration;

    /**
     * @param array<string, mixed> $row
     */
    public static function create(string $table, array $row): self
    {
        return GeneralUtility::makeInstance(self::class, $table, $row);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(protected string $table, protected array $data)
    {
        $this->tableConfiguration = TableConfiguration::fromTableName($this->table);
    }

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getUid(): int
    {
        return $this->data['uid'];
    }

    public function getPid(): int
    {
        return $this->data['pid'];
    }

    public function isRoot(): bool
    {
        return empty($this->getPid());
    }

    public function getLanguageUid(): int
    {
        return $this->data[$this->tableConfiguration->getLanguageField()];
    }

    public function getLanguage(): SiteLanguage
    {
        return GeneralUtility::makeInstance(CurrentSite::class)
            ->get()
            ->getLanguageById($this->getLanguageUid())
        ;
    }

    public function isTranslation(): bool
    {
        return
            $this->tableConfiguration->hasLanguage() &&
            $this->getLanguageUid() > 0;
    }

    public function hasLanguageParent(): bool
    {
        return
            $this->tableConfiguration->hasLanguageParentField() &&
            !empty($this->data[$this->tableConfiguration->getLanguageParentFieldName()]);
    }

    public function hasLanguageField(): bool
    {
        return $this->tableConfiguration->hasLanguage();
    }

    public function getLanguageParentUid(): int
    {
        return $this->data[$this->tableConfiguration->getLanguageParentFieldName()];
    }

    public function isShowAtAnyLogin(): bool
    {
        return in_array('-2', $this->getFrontendGroups(), false);
    }

    /**
     * @return array<int, string>
     */
    public function getFrontendGroups(): array
    {
        $feGroupField = $GLOBALS['TCA'][$this->table]['ctrl']['enablecolumns']['fe_group'] ?? null;

        if (null === $feGroupField) {
            return [];
        }

        $groupList = $this->data[$feGroupField] ?? null;

        if (empty($groupList)) {
            return [];
        }

        return explode(',', $groupList);
    }

    public function assertRootPageLanguageIntegrity(): self
    {
        if ('pages' === $this->table && $this->isRoot() && $this->isTranslation() && !$this->hasLanguageParent()) {
            throw new GraphqlException('Integrity failure: On resolving page children, a root page (uid='.$this->data['uid'].') was found of language='.$this->getLanguageUid().' but without parent-language relation (l10n_parent).');
        }

        return $this;
    }
}
