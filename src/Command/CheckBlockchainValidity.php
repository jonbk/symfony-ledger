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
    name: 'app:check-blockchain',
    description: 'Check the validity of the blockchain',
)]
final class CheckBlockchainValidity extends Command
{
    public function __construct(
        private readonly Blockchain      $blockchain
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if($this->blockchain->checkBlockchainValidity()) {
            $io->success('The blockchain is valid.');
        } else {
            $io->error('The blockchain is not valid.');
        }

        return Command::SUCCESS;
    }
}