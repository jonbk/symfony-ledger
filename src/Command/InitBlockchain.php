<?php

namespace App\Command;

use App\Model\Block;
use App\Service\Blockchain;
use Aws\DynamoDb\DynamoDbClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:init-blockchain',
    description: 'Initialize the DynamoDB database and blockchain',
)]
class InitBlockchain extends Command
{

    public function __construct(
        private readonly string         $tableName,
        private readonly DynamoDbClient $dynamoDbClient,
        private readonly Blockchain     $blockchain
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        //check if table already exists
        $tables = $this->dynamoDbClient->listTables();

        if (in_array($this->tableName, $tables['TableNames'])) {
            $io->warning('La table existe déjà : ' . $this->tableName);
        } else {
            $params = [
                'TableName' => $this->tableName,
                'KeySchema' => [
                    [
                        'AttributeName' => 'uuid',
                        'KeyType' => 'HASH'
                    ]
                ],
                'AttributeDefinitions' => [
                    [
                        'AttributeName' => 'uuid',
                        'AttributeType' => 'S'
                    ],
                    [
                        'AttributeName' => 'action',
                        'AttributeType' => 'S'
                    ],
                    [
                        'AttributeName' => 'identifier',
                        'AttributeType' => 'S'
                    ],
                ],
                'BillingMode' => 'PAY_PER_REQUEST',
                'GlobalSecondaryIndexes' => [
                    [
                        'IndexName' => 'ActionIndex',
                        'KeySchema' => [
                            [
                                'AttributeName' => 'action',
                                'KeyType' => 'HASH'
                            ],
                        ],
                        'Projection' => [
                            'ProjectionType' => 'ALL',
                        ],
                    ],
                    [
                        'IndexName' => 'IdentifierIndex',
                        'KeySchema' => [
                            [
                                'AttributeName' => 'identifier',
                                'KeyType' => 'HASH'
                            ],
                        ],
                        'Projection' => [
                            'ProjectionType' => 'ALL',
                        ],
                    ],
                ],
            ];

            try {
                $this->dynamoDbClient->createTable($params);
                $io->success('Table créée avec succès : ' . $this->tableName);
            } catch (\Exception $e) {
                $io->error('Erreur lors de la création de la table : ' . $e->getMessage());
                return Command::FAILURE;
            }
        }

        //if there is more than 0 blocks, the blockchain is already initialized
        $result = $this->dynamoDbClient->scan([
            'TableName' => $this->tableName,
            'Select' => 'COUNT'
        ]);

        if ($result['Count'] > 0) {
            $io->warning('La blockchain est déjà initialisée');
            return Command::SUCCESS;
        } else {
            $initialBlock = new Block(
                'BLOCKCHAIN_INITIALIZATION',
                'BLOCKCHAIN_INITIALIZATION',
                'ADMINISTRATOR',
                new \DateTimeImmutable(),
                [],
                null
            );

            $this->blockchain->signBlock($initialBlock);
            $this->blockchain->storeBlock($initialBlock);

            $io->succes('La blockchain a été initialisée avec succès');
        }

        return Command::SUCCESS;
    }
}
