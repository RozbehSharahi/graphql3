<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use RozbehSharahi\Graphql3\Exception\BadRequestException;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Exception\NotImplementedException;
use Symfony\Component\Security\Core\User\UserInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class JwtUser implements UserInterface
{
    public const PATTERN_ROLE_GROUP_ID = 'ROLE_GROUP_ID::%s';

    public const ERROR_COULD_NOT_MAP_JWT_TOKEN = 'Jwt token was present, but could not be mapped since it did not contain username (string) and roles (array).';

    /**
     * @param array<int, string> $roles
     */
    public static function create(string $username, array $roles): self
    {
        return GeneralUtility::makeInstance(self::class, $username, $roles);
    }

    /**
     * @param array<string,mixed> $payload
     */
    public static function createFromPayload(array $payload): self
    {
        ['username' => $username, 'roles' => $roles] = $payload;

        if (!is_string($username) || !is_array($roles)) {
            throw new BadRequestException(self::ERROR_COULD_NOT_MAP_JWT_TOKEN);
        }

        return GeneralUtility::makeInstance(self::class, $username, $roles);
    }

    public static function createFromSession(): self
    {
        try {
            /** @var UserAspect $userAspect */
            $userAspect = GeneralUtility::makeInstance(Context::class)->getAspect('frontend.user');

            if (!$userAspect->isLoggedIn()) {
                throw new InternalErrorException('Can not create jwt-user from session without logged in user');
            }

            $username = $userAspect->get('username');
            $groupIds = $userAspect->getGroupIds();
            $groupIds = array_filter($groupIds, static fn (int $groupId) => $groupId > 0);

            $roles = array_map(static fn (int $groupId) => self::createGroupIdRole($groupId), $groupIds);
            $roles = array_values($roles);

            return self::create($username, $roles);
        } catch (AspectNotFoundException|AspectPropertyNotFoundException) {
            throw new InternalErrorException('Could not create frontend user from session.');
        }
    }

    public static function createGroupIdRole(int $id): string
    {
        return sprintf(self::PATTERN_ROLE_GROUP_ID, $id);
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
        return $this->username;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function hasGroupId(int $id): bool
    {
        return $this->hasRole(self::createGroupIdRole($id));
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'username' => $this->username,
            'roles' => $this->roles,
        ];
    }
}
