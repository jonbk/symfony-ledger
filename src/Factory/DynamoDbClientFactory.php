<?php

namespace App\Factory;

use Aws\DynamoDb\DynamoDbClient;

class DynamoDbClientFactory
{
    public static function createDynamoDbClient(string $endpoint, string $region): DynamoDbClient
    {
        return new DynamoDbClient([
            'region' => $region,
            'endpoint' => $endpoint,
            'version' => 'latest',
        ]);
    }
}