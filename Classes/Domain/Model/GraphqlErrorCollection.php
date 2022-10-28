<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\Response;
use TYPO3\CMS\Core\Http\ResponseFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GraphqlErrorCollection
{
    /**
     * @param array<int, mixed> $errors
     */
    public static function createFromOutput(array $errors): self
    {
        return self::create(array_map(static fn ($v) => GraphqlError::create($v['message']), $errors));
    }

    /**
     * @param GraphqlError[] $errors
     */
    public static function create(array $errors, int $code = Response::HTTP_BAD_REQUEST): self
    {
        return GeneralUtility::makeInstance(self::class, $errors, $code);
    }

    /**
     * @param GraphqlError[] $errors
     */
    public function __construct(protected array $errors, protected int $code = Response::HTTP_BAD_REQUEST)
    {
        foreach ($this->errors as $error) {
            if (!$error instanceof GraphqlError) {
                throw new InternalErrorException(self::class.' only allows '.GraphqlError::class.' items.');
            }
        }
    }

    /**
     * @return GraphqlError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function toArray(): array
    {
        return array_map(static fn ($error) => $error->toArray(), $this->errors);
    }

    public function toJson(): string
    {
        try {
            return json_encode(['code' => $this->code, 'errors' => $this->toArray()], JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InternalErrorException('Could not encode graphql error to json: '.$e->getMessage());
        }
    }

    public function toResponse(): ResponseInterface
    {
        $responseFactory = GeneralUtility::makeInstance(ResponseFactory::class);

        $response = $responseFactory
            ->createResponse()
            ->withStatus($this->code)
            ->withHeader('Content-Type', 'application/json')
        ;

        $response->getBody()->write($this->toJson());

        return $response;
    }
}
