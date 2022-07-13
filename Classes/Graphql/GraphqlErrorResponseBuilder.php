<?php

namespace RozbehSharahi\Graphql3\Graphql;

use Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlError;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlErrorCollection;
use RozbehSharahi\Graphql3\Exception\GraphqlException;
use Symfony\Component\HttpFoundation\Response;

class GraphqlErrorResponseBuilder
{
    protected GraphqlErrorCollection $errors;

    public function __construct(protected ResponseFactoryInterface $responseFactory)
    {
        $this->errors = new GraphqlErrorCollection([]);
    }

    public function getErrors(): GraphqlErrorCollection
    {
        return $this->errors;
    }

    public function withErrors(array|GraphqlErrorCollection $errors): self
    {
        $clone = clone $this;
        $clone->errors = $errors instanceof GraphqlErrorCollection ? $errors : new GraphqlErrorCollection($errors);

        return $clone;
    }

    public function withError(GraphqlError $error): self
    {
        $clone = clone $this;
        $clone->errors = new GraphqlErrorCollection([$error]);

        return $clone;
    }

    public function withMessage(string $message): self
    {
        $clone = clone $this;
        $clone->errors = new GraphqlErrorCollection([new GraphqlError($message)]);

        return $clone;
    }

    public function build(): ResponseInterface
    {
        $response = $this
            ->responseFactory
            ->createResponse()
            ->withStatus(Response::HTTP_BAD_REQUEST)
            ->withHeader('Content-Type', 'application/json');

        $response->getBody()->write($this->encode(['errors' => $this->errors->toArray()]));

        return $response;
    }

    protected function encode(array $data): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR);
        } catch (Exception) {
            throw new GraphqlException('GraphQL library responded with unexpected value');
        }
    }
}
