<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlError;
use RozbehSharahi\Graphql3\Exception\BadRequestException;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Exception\NotImplementedException;
use RozbehSharahi\Graphql3\Exception\ShouldNotHappenException;
use RozbehSharahi\Graphql3\Exception\UnauthorizedException;
use Throwable;
use TYPO3\CMS\Core\Core\Environment;

class ErrorHandler
{
    public function __construct(protected LoggerInterface $logger)
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(Throwable $throwable): ResponseInterface
    {
        if ($throwable instanceof BadRequestException) {
            return GraphqlError::create($throwable->getMessage())->toResponse();
        }

        if ($throwable instanceof UnauthorizedException) {
            return GraphqlError::create($throwable->getMessage(), 401)->toResponse();
        }

        if ($throwable instanceof NotImplementedException) {
            return GraphqlError::create($throwable->getMessage(), 501)->toResponse();
        }

        if ($throwable instanceof InternalErrorException && $this->isInternalEnvironment()) {
            $this->logger->critical($throwable->getPrivateMessage());

            return GraphqlError::create($throwable->getPrivateMessage(), 500)->toResponse();
        }

        if ($throwable instanceof InternalErrorException) {
            $this->logger->critical($throwable->getPrivateMessage());

            return GraphqlError::create($throwable->getPublicMessage(), 500)->toResponse();
        }

        if ($throwable instanceof ShouldNotHappenException && $this->isInternalEnvironment()) {
            $this->logger->critical($throwable->getPrivateMessage());

            return GraphqlError::create($throwable->getPrivateMessage())->toResponse();
        }

        if ($throwable instanceof ShouldNotHappenException) {
            $this->logger->critical($throwable->getPrivateMessage());

            return GraphqlError::create($throwable->getPublicMessage())->toResponse();
        }

        throw $throwable;
    }

    public function isInternalEnvironment(): bool
    {
        return !Environment::getContext()->isProduction();
    }
}
