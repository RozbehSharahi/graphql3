<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Resolver;

use RozbehSharahi\Graphql3\Domain\Model\ItemRequest;
use RozbehSharahi\Graphql3\Security\AccessChecker;
use RozbehSharahi\Graphql3\Session\CurrentSession;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class LanguageResolver
{
    public function __construct(
        protected CurrentSession $currentSession,
        protected AccessChecker $accessChecker,
    ) {
    }

    public function resolve(ItemRequest $request): ?SiteLanguage
    {
        try {
            $language = $this->currentSession->getSite()->getLanguageById((int) $request->get('id'));
        } catch (\Throwable) {
            return null;
        }

        $this->accessChecker->assert(['VIEW'], $language);

        return $language;
    }
}
