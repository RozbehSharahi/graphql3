<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Session;

use RozbehSharahi\Graphql3\Domain\Model\JwtUser;
use RozbehSharahi\Graphql3\Exception\BadRequestException;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Security\JwtManager;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class CurrentSession implements SingletonInterface
{
    protected SiteInterface $site;

    protected ServerRequest $request;

    public function __construct(protected JwtManager $jwtManager)
    {
    }

    public function setRequest(ServerRequest $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function getRequest(): ServerRequest
    {
        return $this->request;
    }

    public function getSite(): SiteInterface
    {
        return $this->request->getAttribute('site');
    }

    public function getUser(): JwtUser
    {
        return $this->hasToken() ? $this->fetchUserFromToken() : JwtUser::createFromTypo3Session();
    }

    public function getToken(): string
    {
        if (!$this->hasToken()) {
            throw new InternalErrorException('Request did not contain jwt token, please use hasToken to avoid this exception.');
        }

        return str_replace('Bearer ', '', $this->getRequest()->getHeaderLine('Authorization'));
    }

    public function hasUser(): bool
    {
        try {
            return (bool) $this->getUser();
        } catch (\Throwable) {
            return false;
        }
    }

    public function hasToken(): bool
    {
        return
            $this->getRequest()->hasHeader('Authorization') &&
            str_starts_with($this->getRequest()->getHeaderLine('Authorization'), 'Bearer ')
        ;
    }

    protected function fetchUserFromToken(): JwtUser
    {
        $token = $this->getToken();
        $jwtManager = $this->jwtManager->withEnvironmentVariables();

        if ($jwtManager->isExpired($token)) {
            throw new BadRequestException('Expired jwt token provided.');
        }

        if (!$jwtManager->isValid($token)) {
            throw new BadRequestException('Invalid jwt token provided.');
        }

        return JwtUser::createFromPayload($jwtManager->read($token));
    }

    public function isLanguageCodeAvailable(string $code): bool
    {
        foreach ($this->getSite()->getLanguages() as $language) {
            if ($language->getTwoLetterIsoCode() === $code) {
                return true;
            }
        }

        return false;
    }

    public function getLanguageByCode(string $code): SiteLanguage
    {
        foreach ($this->getSite()->getLanguages() as $language) {
            if ($language->getTwoLetterIsoCode() === $code) {
                return $language;
            }
        }

        throw new InternalErrorException('Given language code is not available on current site.');
    }
}
