<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Exception\NotImplementedException;
use Symfony\Component\Security\Core\User\UserInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class JwtUser implements UserInterface
{
    /**
     * @param array<int, string> $roles
     */
    public static function create(string $username, array $roles): self
    {
        return GeneralUtility::makeInstance(self::class, $username, $roles);
    }

    /**
     * @param array<int, string> $roles
     */
    public function __construct(protected string $username, protected array $roles)
    {
        foreach ($this->roles as $role) {
            if (!is_string($role)) {
                throw new InternalErrorException('Roles on jwt-user must be an array of strings.');
            }
        }
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): self
    {
        throw new NotImplementedException();
    }

    public function getUserIdentifier(): string
    {
        throw new NotImplementedException();
    }
}
