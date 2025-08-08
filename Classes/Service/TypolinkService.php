<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Service;

use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Session\CurrentSession;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\SingletonInterface;

class TypolinkService implements SingletonInterface
{
    public function __construct(
        protected LinkService $linkService,
        protected CurrentSession $currentSession,
    ) {
    }

    public function parse(string $typolink): string
    {
        try {
            $result = $this->linkService->resolve($typolink);
        } catch (\Throwable $e) {
            throw new InternalErrorException("Could not create link for: {$typolink}", $e->getCode(), $e);
        }

        if (LinkService::TYPE_URL === $result['type']) {
            return $result[LinkService::TYPE_URL];
        }

        if (LinkService::TYPE_EMAIL === $result['type']) {
            return "mailto:{$result[LinkService::TYPE_EMAIL]}";
        }

        if (LinkService::TYPE_TELEPHONE === $result['type']) {
            return "tel:{$result[LinkService::TYPE_TELEPHONE]}";
        }

        if ('page' === $result['type']) {
            return $this->createPageLink((int) $result['pageuid']);
        }

        throw new InternalErrorException("Could not create link for: {$typolink} as this type is not yet supported");
    }

    protected function createPageLink(int $pageUid): string
    {
        return $this->currentSession->getSite()->getRouter()->generateUri($pageUid)->__toString();
    }
}
