<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Session;

use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use TYPO3\CMS\Core\Http\ServerRequest;

class CurrentRequest
{
    protected ServerRequest $request;

    public function hasToken(): bool
    {
        return
            $this->get()->hasHeader('Authorization')
            && str_starts_with($this->get()->getHeaderLine('Authorization'), 'Bearer ');
    }

    public function getToken(): string
    {
        if (!$this->hasToken()) {
            throw new InternalErrorException('Request did not contain jwt token, please use hasToken to avoid this exception.');
        }

        $authorization = $this->get()->getHeaderLine('Authorization');

        return str_replace('Bearer ', '', $authorization);
    }

    public function get(): ServerRequest
    {
        return $this->request;
    }

    public function set(ServerRequest $serverRequest): self
    {
        $this->request = $serverRequest;

        return $this;
    }
}
