<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Exception;

use Throwable;

class UnauthorizedException extends \RuntimeException implements Graphql3ExceptionInterface
{
    public const DEFAULT_MESSAGE = 'Access denied.';

    public function __construct(string $message = self::DEFAULT_MESSAGE, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
