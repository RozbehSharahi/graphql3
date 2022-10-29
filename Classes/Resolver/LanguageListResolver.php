<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Resolver;

use RozbehSharahi\Graphql3\Security\AccessChecker;
use RozbehSharahi\Graphql3\Session\CurrentSite;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class LanguageListResolver
{
    public function __construct(
        protected CurrentSite $currentSite,
        protected AccessChecker $accessChecker
    ) {
    }

    public function getCallable(): callable
    {
        return function () {
            return $this->resolve();
        };
    }

    /**
     * @return array<int, SiteLanguage>
     */
    public function resolve(): array
    {
        $languages = $this->currentSite->get()->getLanguages();

        foreach ($languages as $language) {
            $this->accessChecker->assert(['VIEW'], $language);
        }

        return $languages;
    }
}
