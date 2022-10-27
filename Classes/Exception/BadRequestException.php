<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Exception;

use GraphQL\Error\ClientAware;

class BadRequestException extends \RuntimeException implements ClientAware
{
    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'bad-request';
    }
}
