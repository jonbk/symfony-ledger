<?php

namespace App\Service;

use App\Entity\Block;
use App\Repository\BlockRepository;

final readonly class Blockchain
{
    public function __construct(
        private BlockRepository $blockRepository,
        private string          $privateKey,
        private string          $publicKey
    )
    {
    }

    /*
     * TODO add locking mechanism
     */
    public function addBlock(
        string             $action,
        string             $identifier,
        string             $author,
        \DateTimeImmutable $date,
        array              $metadata
    ): Block
    {
        $latestBlock = $this->blockRepository->findLatest();

        $block = new Block(
            $action,
            $identifier,
            $author,
            $date,
            $metadata,
            $latestBlock instanceof Block ? $latestBlock->getSignature() : null
        );

        $this->signBlock($block);

        $this->blockRepository->save($block);

        return $block;
    }

    private function signBlock(Block $block): void
    {
        openssl_sign($block->payloadToSign(), $signature, $this->privateKey);

        $block->setSignature(base64_encode($signature));
    }
}