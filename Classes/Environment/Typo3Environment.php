<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Environment;

use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class Typo3Environment
{
    public function getMainVersion(): int
    {
        return (int) explode('.', VersionNumberUtility::getCurrentTypo3Version())[0];
    }

    public function getSecondLevelVersion(): int
    {
        return (int) explode('.', VersionNumberUtility::getCurrentTypo3Version())[1];
    }

    public function isVersion(int $version, ?int $secondLevelVersion = null): bool
    {
        if (null !== $secondLevelVersion && $secondLevelVersion !== $this->getSecondLevelVersion()) {
            return false;
        }

        return $this->getMainVersion() === $version;
    }

    public function isGreaterOrEqualVersion(int $version, int $secondLevelVersion = 0): bool
    {
        return $this->getMainVersion() >= $version && $this->getSecondLevelVersion() >= $secondLevelVersion;
    }
}
