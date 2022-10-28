<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GraphqlError
{
    public static function create(string $message, int $code = Response::HTTP_BAD_REQUEST): self
    {
        return GeneralUtility::makeInstance(self::class, $message, $code);
    }

    public function __construct(protected string $message, protected int $code)
    {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function toResponse(): ResponseInterface
    {
        return GraphqlErrorCollection::create([$this], $this->code)->toResponse();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'code' => $this->code,
        ];
    }
}
