<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Security;

use RozbehSharahi\Graphql3\Domain\Model\FrontendUser;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Exception\NotImplementedException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Token implements TokenInterface
{
    public function __construct(protected ?FrontendUser $frontendUser = null)
    {
        $this->frontendUser = $this->frontendUser ?: FrontendUser::fromContext();
    }

    public function __toString(): string
    {
        return 'user:'.$this->frontendUser->getUserIdentifier();
    }

    public function getUserIdentifier(): string
    {
        return $this->frontendUser->getUserIdentifier() ?: 'anonymous';
    }

    /**
     * @return array<int, string>
     */
    public function getRoleNames(): array
    {
        return $this->frontendUser->isLoggedIn() ? $this->frontendUser->getGroupNames() : [];
    }

    public function getUser(): ?UserInterface
    {
        return $this->frontendUser->isLoggedIn() ? $this->frontendUser : null;
    }

    public function setUser(UserInterface $user): self
    {
        if (!$user instanceof FrontendUser) {
            throw new InternalErrorException('Can only set user of type: '.FrontendUser::class.' on token.');
        }

        $this->frontendUser = $user;

        return $this;
    }

    public function eraseCredentials(): self
    {
        throw new NotImplementedException('Not implemented');
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        throw new NotImplementedException('Not implemented');
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function setAttributes(array $attributes): self
    {
        throw new NotImplementedException('Not implemented');
    }

    public function hasAttribute(string $name): bool
    {
        throw new NotImplementedException('Not implemented');
    }

    public function getAttribute(string $name): mixed
    {
        throw new NotImplementedException('Not implemented');
    }

    public function setAttribute(string $name, mixed $value): self
    {
        throw new NotImplementedException('Not implemented');
    }

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        throw new NotImplementedException('Not implemented');
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        throw new NotImplementedException('Not implemented');
    }
}