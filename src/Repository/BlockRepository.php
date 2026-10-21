<?php

namespace App\Repository;

use App\Doctrine\Type\DateTimeImmutableMsType;
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
            ->orderBy('b.timestamp', \SortDirection::Descending)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Block $block): void
    {
        $this->getEntityManager()->persist($block);
        $this->getEntityManager()->flush();
    }

    /**
     * @return list<Block>
     */
    public function findBatch(?\DateTimeImmutable $after, \DateTimeImmutable $until, int $limit): array
    {
        $qb = $this->createQueryBuilder('b')
            ->where('b.timestamp <= :until')
            ->setParameter('until', $until, DateTimeImmutableMsType::NAME)
            ->orderBy('b.timestamp', \SortDirection::Ascending)
            ->setMaxResults($limit);

        if ($after !== null) {
            $qb
                ->andWhere('b.timestamp > :after')
                ->setParameter('after', $after, DateTimeImmutableMsType::NAME);
        }

        return $qb->getQuery()->getResult();
    }

    public function clear(): void
    {
        $this->getEntityManager()->clear();
    }
}