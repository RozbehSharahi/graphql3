<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Session;

use RozbehSharahi\Graphql3\Domain\Model\JwtUser;
use RozbehSharahi\Graphql3\Exception\BadRequestException;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Security\JwtManager;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class CurrentSession implements SingletonInterface
{
    public const ERROR_JWT_EXPIRED = 'Expired jwt token provided.';

    public const ERROR_JWT_INVALID = 'Invalid jwt token provided.';

    public const ERROR_NO_TOKEN_ON_REQUEST = 'Request did not contain jwt token, please use hasToken to avoid this exception.';

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

    public function getSite(): Site
    {
        return $this->request->getAttribute('site');
    }

    public function hasUser(): bool
    {
        try {
            return (bool) $this->getUser();
        } catch (\Throwable) {
            return false;
        }
    }

    public function getUser(): JwtUser
    {
        return $this->hasToken() ? $this->fetchUserFromToken() : JwtUser::createFromTypo3Session();
    }

    public function hasToken(): bool
    {
        return
            $this->getRequest()->hasHeader('Authorization')
            && str_starts_with($this->getRequest()->getHeaderLine('Authorization'), 'Bearer ');
    }

    public function getToken(): string
    {
        if (!$this->hasToken()) {
            throw new InternalErrorException(self::ERROR_NO_TOKEN_ON_REQUEST);
        }

        return str_replace('Bearer ', '', $this->getRequest()->getHeaderLine('Authorization'));
    }

    public function assertTokenIsValid(): self
    {
        $token = $this->getToken();
        $jwtManager = $this->jwtManager->withEnvironmentVariables();

        if ($jwtManager->isExpired($token)) {
            throw new BadRequestException(self::ERROR_JWT_EXPIRED);
        }

        if (!$jwtManager->isValid($token)) {
            throw new BadRequestException(self::ERROR_JWT_INVALID);
        }

        return $this;
    }

    public function fetchUserFromToken(): JwtUser
    {
        $token = $this->assertTokenIsValid()->getToken();
        $jwtManager = $this->jwtManager->withEnvironmentVariables();

        return JwtUser::createFromPayload($jwtManager->read($token));
    }

    public function isLanguageCodeAvailable(string $code): bool
    {
        foreach ($this->getSite()->getLanguages() as $language) {
            if ($language->getLocale()->getLanguageCode() === $code) {
                return true;
            }
        }

        return false;
    }

    public function getLanguageByCode(string $code): SiteLanguage
    {
        foreach ($this->getSite()->getLanguages() as $language) {
            if ($language->getLocale()->getLanguageCode() === $code) {
                return $language;
            }
        }

        throw new InternalErrorException('Given language code is not available on current site.');
    }
}
