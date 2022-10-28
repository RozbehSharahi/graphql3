<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Handler;

use GraphQL\Error\Error;
use GraphQL\Error\SyntaxError;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlError;
use RozbehSharahi\Graphql3\Exception\BadRequestException;
use RozbehSharahi\Graphql3\Exception\Graphql3ExceptionInterface;
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

    public function handle(Throwable $throwable): ResponseInterface
    {
        // In some cases our exceptions are wrapped into a graphql error exception.
        // Probably those on execution time
        $exception = !$throwable->getPrevious() instanceof Graphql3ExceptionInterface
            ? $throwable
            : $throwable->getPrevious();

        if ($exception instanceof Error && $exception->isClientSafe()) {
            return GraphqlError::create($exception->getMessage())->toResponse();
        }

        if ($exception instanceof SyntaxError) {
            return GraphqlError::create($exception->getMessage())->toResponse();
        }

        if ($exception instanceof BadRequestException) {
            return GraphqlError::create($exception->getMessage())->toResponse();
        }

        if ($exception instanceof UnauthorizedException) {
            return GraphqlError::create($exception->getMessage(), 401)->toResponse();
        }

        if ($exception instanceof NotImplementedException) {
            return GraphqlError::create($exception->getMessage(), 501)->toResponse();
        }

        if ($exception instanceof InternalErrorException && $this->isInternalEnvironment()) {
            $this->logger->critical($exception->getPrivateMessage());

            return GraphqlError::create($exception->getPrivateMessage(), 500)->toResponse();
        }

        if ($exception instanceof InternalErrorException) {
            $this->logger->critical($exception->getPrivateMessage());

            return GraphqlError::create($exception->getPublicMessage(), 500)->toResponse();
        }

        if ($exception instanceof ShouldNotHappenException && $this->isInternalEnvironment()) {
            $this->logger->critical($exception->getPrivateMessage());

            return GraphqlError::create($exception->getPrivateMessage())->toResponse();
        }

        if ($exception instanceof ShouldNotHappenException) {
            $this->logger->critical($exception->getPrivateMessage());

            return GraphqlError::create($exception->getPublicMessage())->toResponse();
        }

        $this->logger->critical('Unhandled exception: '.$exception->getMessage());

        return GraphqlError::create(InternalErrorException::PUBLIC_MESSAGE)->toResponse();
    }

    public function isInternalEnvironment(): bool
    {
        return !Environment::getContext()->isProduction();
    }
}
