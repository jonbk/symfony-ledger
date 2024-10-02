<?php

namespace App\Service;

use App\Model\Block;
use Aws\DynamoDb\DynamoDbClient;

final readonly class Blockchain
{
    public function __construct(
        private DynamoDbClient $dynamoDbClient,
        private string         $tableName,
        private string         $privateKey,
        private string         $publicKey
    )
    {
    }

    public function signBlock(Block $new): void
    {
        openssl_sign($new->toSign(), $signature, $this->privateKey);

        $new->setSignature(base64_encode($signature));
    }

    public function storeBlock(Block $block): void
    {
        $this->dynamoDbClient->putItem([
            'TableName' => $this->tableName,
            'Item' => [
                'uuid' => ['S' => $block->getUuid()->toString()],
                'timestamp' => ['S' => $block->getTimestamp()->format(DATE_ATOM)],
                'action' => ['S' => $block->getAction()],
                'identifier' => ['S' => $block->getIdentifier()],
                'author' => ['S' => $block->getAuthor()],
                'date' => ['S' => $block->getDate()->format(DATE_ATOM)],
                'metadata' => ['S' => json_encode($block->getMetadata())],
                'previousSignature' => $block->getPreviousSignature() ? ['S' => $block->getPreviousSignature()] : ['NULL' => true],
                'signature' => ['S' => $block->getSignature()],
            ]
        ]);
    }
}