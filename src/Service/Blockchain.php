<?php

namespace App\Service;

use App\Entity\Block;
use App\Repository\BlockRepository;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\LockFactory;

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
                $latestBlock instanceof Block ? $latestBlock->getSignature() : null
            );

            openssl_sign($block->payloadToSign(), $signature, $this->privateKey);

            $block
                ->setSignature(base64_encode($signature))
                ->setSignatureVerified(true);


            $this->blockRepository->save($block);

            // Ensure minimum time between blocks timestamp of 1 microsecond
            usleep(1);
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
            ->orderBy('b.timestamp', 'DESC')
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

    public function checkBlockchainValidity(): ?Block
    {
        ini_set('memory_limit', '-1');

        $blocksUuids = $this->blockRepository->findAllUuids();

        $previousBlock = null;
        foreach ($blocksUuids as $uuid) {
            $block = $this->blockRepository->find($uuid);

            if (
                $previousBlock instanceof Block
                && $block->getPreviousSignature() !== $previousBlock->getSignature()
            ) {
                return $block;
            }

            if (!$this->verifySignature($block)) {
                return $block;
            }

            $previousBlock = $block;

            $this->blockRepository->clear();
        }

        return null;
    }

    public function countBlocks(): int
    {
        return $this->blockRepository->count();
    }
}