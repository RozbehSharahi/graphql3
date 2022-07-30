<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Encoder;

use Exception;
use RozbehSharahi\Graphql3\Exception\GraphqlException;

class JsonEncoder
{
    public function encode(array $data): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR);
        } catch (Exception) {
            throw new GraphqlException('Mal formed json');
        }
    }

    public function decode(string $json): mixed
    {
        try {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception) {
            throw new GraphqlException('Could not encode to json');
        }
    }

    public function isValidJson(string $json): bool
    {
        try {
            $this->decode($json);

            return true;
        } catch (Exception) {
            return false;
        }
    }
}
