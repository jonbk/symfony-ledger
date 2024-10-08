<?php

namespace App\Repository;

use App\Entity\Block;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class BlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Block::class);
    }

    public function findLatest(): ?Block
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.timestamp', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Block $block): void
    {
        $this->getEntityManager()->persist($block);
        $this->getEntityManager()->flush();
    }

    public function findAllUuids(): array
    {
        return $this->createQueryBuilder('b')
            ->select('b.uuid')
            ->orderBy('b.timestamp', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function clear(): void
    {
        $this->getEntityManager()->clear();
    }
}