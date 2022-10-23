<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Exception;

use GraphQL\Error\ClientAware;

class GraphqlException extends \RuntimeException implements ClientAware
{
    protected bool $clientSafe = false;

    public static function createClientSafe(string $message): self
    {
        return (new self($message))->setClientSafe(true);
    }

    public function setClientSafe(bool $clientSafe): self
    {
        $this->clientSafe = $clientSafe;

        return $this;
    }

    public function isClientSafe(): bool
    {
        return $this->clientSafe;
    }

    public function getCategory(): string
    {
        return 'typo3';
    }
}
