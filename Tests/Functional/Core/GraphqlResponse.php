<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional\Core;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\ArrayUtility;

class GraphqlResponse extends Response
{
    public static function fromResponse(ResponseInterface $response): self
    {
        return new self(
            $response->getBody(),
            $response->getStatusCode(),
            $response->getHeaders(),
            $response->getReasonPhrase()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        $responseBody = (string) $this->getBody();

        try {
            return json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new \RuntimeException('Could not parse graphql response in test: '.$responseBody);
        }
    }

    public function get(string $path): mixed
    {
        return ArrayUtility::getValueByPath($this->getData(), $path, '.');
    }

    public function getErrorMessage(): string
    {
        return $this->get('errors.0.message');
    }
}
