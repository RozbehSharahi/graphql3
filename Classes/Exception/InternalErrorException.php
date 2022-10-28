<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Exception;

class InternalErrorException extends \RuntimeException implements Graphql3ExceptionInterface
{
    public const PUBLIC_MESSAGE = 'An internal error occurred. ';

    public function getPublicMessage(): string
    {
        return self::PUBLIC_MESSAGE;
    }

    public function getPrivateMessage(): string
    {
        return self::PUBLIC_MESSAGE.$this->message;
    }
}
