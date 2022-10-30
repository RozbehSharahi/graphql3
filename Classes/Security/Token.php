<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Security;

use RozbehSharahi\Graphql3\Domain\Model\JwtUser;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Exception\NotImplementedException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Token implements TokenInterface
{
    private ?JwtUser $user;

    public function __construct()
    {
    }

    public function __toString(): string
    {
        return 'user::'.$this->getUserIdentifier();
    }

    public function getUserIdentifier(): string
    {
        return $this->getUser()?->getUserIdentifier() ?: 'anonymous';
    }

    /**
     * @return array<int, string>
     */
    public function getRoleNames(): array
    {
        return $this->getUser()?->getRoles() ?: [];
    }

    public function getUser(): ?JwtUser
    {
        return $this->user ?? null;
    }

    public function setUser(?UserInterface $user): self
    {
        if ($user && !$user instanceof JwtUser) {
            throw new InternalErrorException('Only jwt-users are allowed to be set on token.');
        }

        $this->user = $user;

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
