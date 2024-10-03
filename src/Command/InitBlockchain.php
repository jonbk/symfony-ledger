<?php

namespace App\Command;

use App\Entity\Block;
use App\Repository\BlockRepository;
use App\Service\Blockchain;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:init-blockchain',
    description: 'Initialize blockchain',
)]
final class InitBlockchain extends Command
{
    public function __construct(
        private readonly Blockchain      $blockchain,
        private readonly BlockRepository $blockRepository,
        private readonly LoggerInterface $logger
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $block = $this->blockRepository->findLatest();

        if ($block instanceof Block) {
            $io->warning('La blockchain est déjà initialisée');
        } else {

            $this->blockchain->addBlock(
                'BLOCKCHAIN_INITIALIZATION',
                'BLOCKCHAIN_INITIALIZATION',
                'ADMINISTRATOR',
                new DateTimeImmutable(),
                []
            );

            $io->success('Blockchain initialisée avec succès.');
        }

        return Command::SUCCESS;
    }
}