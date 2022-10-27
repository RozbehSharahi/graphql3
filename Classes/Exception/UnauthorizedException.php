<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Exception;

use GraphQL\Error\ClientAware;
use Throwable;

class UnauthorizedException extends \RuntimeException implements ClientAware
{
    public function __construct(string $message = 'Access denied.', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'unauthorized';
    }
}
