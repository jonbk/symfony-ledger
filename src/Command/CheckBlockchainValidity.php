<?php

namespace App\Command;

use App\Service\Blockchain;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Uuid;

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

    protected function configure(): void
    {
        $this->addOption('anchor', null, InputOption::VALUE_REQUIRED, 'UUID of the last block validated by a previous check, which must still be in the chain');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $anchor = $input->getOption('anchor');

        if ($anchor !== null && !Uuid::isValid($anchor)) {
            $io->error(sprintf('The anchor %s is not a valid UUID.', $anchor));

            return Command::INVALID;
        }

        $validity = $this->blockchain->checkBlockchainValidity($anchor !== null ? Uuid::fromString($anchor) : null);

        if (!$validity->isValid()) {
            $io->error(array_filter([
                sprintf('The blockchain is not valid: %s', $validity->error),
                $validity->invalidBlock !== null ? sprintf('Invalid block: %s', $validity->invalidBlock->getUuid()) : null,
                $validity->invalidBlock !== null ? sprintf('Payload: %s', $validity->invalidBlock->payloadToSign()) : null,
                sprintf('Last valid block: %s', $validity->lastBlock?->getUuid() ?? 'none'),
                sprintf('Checked blocks: %d', $validity->checkedBlocks),
            ]));

            return Command::FAILURE;
        }

        $io->success([
            'The blockchain is valid.',
            sprintf('Checked blocks: %d', $validity->checkedBlocks),
            sprintf('Last block: %s (%s)', $validity->lastBlock?->getUuid() ?? 'none', $validity->lastBlock?->getTimestamp()->format('Y-m-d\TH:i:s.uP') ?? '-'),
        ]);

        return Command::SUCCESS;
    }
}
