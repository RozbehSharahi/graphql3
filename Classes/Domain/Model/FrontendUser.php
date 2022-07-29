<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use RozbehSharahi\Graphql3\Exception\GraphqlException;
use Symfony\Component\Security\Core\User\UserInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FrontendUser implements UserInterface
{
    public static function fromContext(): self
    {
        try {
            /** @var UserAspect $userAspect */
            $userAspect = GeneralUtility::makeInstance(Context::class)->getAspect('frontend.user');

            return GeneralUtility::makeInstance(
                self::class,
                $userAspect->get('id'),
                $userAspect->get('username'),
                $userAspect->getGroupNames(),
                $userAspect->isLoggedIn()
            );
        } catch (AspectNotFoundException|AspectPropertyNotFoundException) {
            throw new GraphqlException('Could create frontend user from context.');
        }
    }

    public function __construct(
        protected ?int $id,
        protected string $userName,
        protected array $groupNames,
        protected bool $loggedIn
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return $this->userName;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function getRoles(): array
    {
        return $this->groupNames;
    }

    public function getGroupNames(): array
    {
        return $this->groupNames;
    }

    public function isLoggedIn(): bool
    {
        return $this->loggedIn;
    }

    public function eraseCredentials(): self
    {
        return $this;
    }
}
