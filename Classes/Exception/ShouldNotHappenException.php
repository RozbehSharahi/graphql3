<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Exception;

class ShouldNotHappenException extends \RuntimeException implements Graphql3ExceptionInterface
{
    /**
     * @param array<int|string, mixed> $debugData
     */
    public function __construct(string $message, protected array $debugData)
    {
        parent::__construct($message);
    }

    public function getPublicMessage(): string
    {
        return InternalErrorException::PUBLIC_MESSAGE;
    }

    public function getPrivateMessage(): string
    {
        return 'A should-not-happen-error occurred: '.$this->message.' '.$this->getDebugDataJson();
    }

    public function getDebugDataJson(): string
    {
        /* @noinspection JsonEncodingApiUsageInspection */
        return json_encode($this->debugData);
    }
}
