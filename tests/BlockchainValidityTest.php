<?php

namespace App\Tests;

use App\Entity\Block;
use App\Service\Blockchain;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BlockchainValidityTest extends KernelTestCase
{
    private Blockchain $blockchain;
    private Connection $connection;

    /** @var list<Block> */
    private array $blocks = [];

    protected function setUp(): void
    {
        self::bootKernel();

        $this->blockchain = static::getContainer()->get(Blockchain::class);
        $this->connection = static::getContainer()->get('doctrine.dbal.default_connection');

        $this->connection->executeStatement('DELETE FROM block');

        $this->blocks = [];
        for ($i = 0; $i < 7; $i++) {
            $this->blocks[] = $this->blockchain->addBlock('ACTION', 'block-' . $i, 'tests', new \DateTimeImmutable(), ['index' => $i]);
        }
    }

    public function testValidChainAcrossBatches(): void
    {
        $validity = $this->blockchain->checkBlockchainValidity(null, 3);

        $this->assertTrue($validity->isValid());
        $this->assertSame(7, $validity->checkedBlocks);
        $this->assertTrue($validity->lastBlock->getUuid()->equals($this->blocks[6]->getUuid()));
    }

    public function testEmptyChainIsValid(): void
    {
        $this->connection->executeStatement('DELETE FROM block');

        $validity = $this->blockchain->checkBlockchainValidity();

        $this->assertTrue($validity->isValid());
        $this->assertSame(0, $validity->checkedBlocks);
    }

    public function testTamperedBlockAtBatchBoundary(): void
    {
        $this->connection->executeStatement('UPDATE block SET metadata = \'{"index":99}\' WHERE uuid = ?', [$this->blocks[3]->getUuid()->toRfc4122()]);

        $validity = $this->blockchain->checkBlockchainValidity(null, 3);

        $this->assertFalse($validity->isValid());
        $this->assertSame(3, $validity->checkedBlocks);
        $this->assertTrue($validity->invalidBlock->getUuid()->equals($this->blocks[3]->getUuid()));
    }

    public function testRemovedBlockBreaksTheChain(): void
    {
        $this->deleteBlock($this->blocks[3]);

        $validity = $this->blockchain->checkBlockchainValidity(null, 3);

        $this->assertFalse($validity->isValid());
        $this->assertTrue($validity->invalidBlock->getUuid()->equals($this->blocks[4]->getUuid()));
    }

    public function testRemovedHeadIsDetected(): void
    {
        $this->deleteBlock($this->blocks[0]);

        $validity = $this->blockchain->checkBlockchainValidity();

        $this->assertFalse($validity->isValid());
        $this->assertSame(0, $validity->checkedBlocks);
        $this->assertTrue($validity->invalidBlock->getUuid()->equals($this->blocks[1]->getUuid()));
    }

    public function testRemovedTailIsDetectedWithAnchor(): void
    {
        $this->assertTrue($this->blockchain->checkBlockchainValidity($this->blocks[6]->getUuid())->isValid());

        $this->deleteBlock($this->blocks[6]);

        $this->assertTrue($this->blockchain->checkBlockchainValidity()->isValid());
        $this->assertFalse($this->blockchain->checkBlockchainValidity($this->blocks[6]->getUuid())->isValid());
    }

    public function testTimestampsAreStrictlyIncreasing(): void
    {
        for ($i = 1; $i < count($this->blocks); $i++) {
            $this->assertGreaterThan($this->blocks[$i - 1]->getTimestamp(), $this->blocks[$i]->getTimestamp());
        }
    }

    private function deleteBlock(Block $block): void
    {
        $this->connection->executeStatement('DELETE FROM block WHERE uuid = ?', [$block->getUuid()->toRfc4122()]);
    }
}
