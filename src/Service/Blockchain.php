<?php

namespace App\Service;

use App\Entity\Block;
use App\Repository\BlockRepository;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Uid\Uuid;

final readonly class Blockchain
{
    public function __construct(
        private LockFactory     $lockFactory,
        private BlockRepository $blockRepository,
        private string          $privateKey,
        private string          $publicKey
    )
    {
    }

    public function addBlock(
        string             $action,
        string             $identifier,
        string             $author,
        \DateTimeImmutable $date,
        array              $metadata
    ): Block
    {
        $lock = $this->lockFactory->createLock('ledger_lock');

        try {
            $lock->acquire(true);

            $latestBlock = $this->blockRepository->findLatest();

            $block = new Block(
                $action,
                $identifier,
                $author,
                $date,
                $metadata,
                $latestBlock
            );

            openssl_sign($block->payloadToSign(), $signature, $this->privateKey);

            $block
                ->setSignature(base64_encode($signature))
                ->setSignatureVerified(true);


            $this->blockRepository->save($block);
        } catch (LockConflictedException $e) {
            throw new \RuntimeException('Impossible d\'ajouter un bloc pour le moment.', 0, $e);
        } finally {
            if ($lock->isAcquired()) {
                $lock->release();
            }
        }

        return $block;
    }

    public function getBlocks(int $page, int $limit = 25): array
    {
        $qb = $this->blockRepository->createQueryBuilder('b')
            ->orderBy('b.timestamp', \SortDirection::Descending)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function verifySignature(Block $block): bool
    {
        $payload = $block->payloadToSign();

        $valid = openssl_verify($payload, base64_decode($block->getSignature()), $this->publicKey) === 1;

        $block->setSignatureVerified($valid);

        return $valid;
    }

    public function checkBlockchainValidity(?Uuid $anchor = null, int $batchSize = 1000): BlockchainValidity
    {
        $until = $this->blockRepository->findLatest()?->getTimestamp();
        $this->blockRepository->clear();

        $checkedBlocks = 0;
        $previousBlock = null;
        $anchorFound = $anchor === null;

        if ($until !== null) {
            do {
                $blocks = $this->blockRepository->findBatch($previousBlock?->getTimestamp(), $until, $batchSize);

                foreach ($blocks as $block) {
                    if ($block->getPreviousSignature() !== $previousBlock?->getSignature()) {
                        return new BlockchainValidity(
                            $checkedBlocks,
                            $previousBlock,
                            $block,
                            $previousBlock === null
                                ? 'The first block of the chain references a previous signature.'
                                : 'The previous signature does not match the signature of the previous block.'
                        );
                    }

                    if (!$this->verifySignature($block)) {
                        return new BlockchainValidity($checkedBlocks, $previousBlock, $block, 'The signature is invalid.');
                    }

                    $anchorFound = $anchorFound || $block->getUuid()->equals($anchor);
                    $previousBlock = $block;
                    $checkedBlocks++;
                }

                $this->blockRepository->clear();
            } while (count($blocks) === $batchSize);
        }

        if (!$anchorFound) {
            return new BlockchainValidity($checkedBlocks, $previousBlock, null, sprintf('The anchor block %s is missing from the chain.', $anchor));
        }

        return new BlockchainValidity($checkedBlocks, $previousBlock);
    }

    public function countBlocks(): int
    {
        return $this->blockRepository->count();
    }
}