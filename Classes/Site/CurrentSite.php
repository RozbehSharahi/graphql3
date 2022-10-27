<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Site;

use RozbehSharahi\Graphql3\Exception\GraphqlException;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class CurrentSite
{
    protected SiteInterface $site;

    public function __construct()
    {
    }

    public function set(SiteInterface $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function get(): SiteInterface
    {
        return $this->site;
    }

    public function isLanguageCodeAvailable(string $code): bool
    {
        foreach ($this->get()->getLanguages() as $language) {
            if ($language->getTwoLetterIsoCode() === $code) {
                return true;
            }
        }

        return false;
    }

    public function getLanguageByCode(string $code): SiteLanguage
    {
        foreach ($this->get()->getLanguages() as $language) {
            if ($language->getTwoLetterIsoCode() === $code) {
                return $language;
            }
        }

        throw GraphqlException::createClientSafe('Given language code is not available on current site.');
    }
}
