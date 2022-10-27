<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use RozbehSharahi\Graphql3\Exception\InternalErrorException;
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
                $userAspect->getGroupIds(),
                $userAspect->isLoggedIn()
            );
        } catch (AspectNotFoundException|AspectPropertyNotFoundException) {
            throw new InternalErrorException('Could not create frontend user from context.');
        }
    }

    /**
     * @param array<int, string> $groupNames
     * @param array<int, int>    $groupIds
     */
    public function __construct(
        protected ?int $id,
        protected string $userName,
        protected array $groupNames,
        protected array $groupIds,
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

    /**
     * @return array<int, string>
     */
    public function getGroupNames(): array
    {
        return $this->groupNames;
    }

    /**
     * @return array<int, int>
     */
    public function getGroupIds(): array
    {
        return $this->groupIds;
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
