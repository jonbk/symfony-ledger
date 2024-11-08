<?php

namespace App\Tests;

use App\Service\Blockchain;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BlockchainTest extends KernelTestCase
{
    public function testSomething(): void
    {
        $kernel = self::bootKernel();

        $container = static::getContainer();

        /** @var Blockchain $blockchain */
        $blockchain = $container->get(Blockchain::class);
        $em = $container->get('doctrine')->getManager();

        $data = json_decode(file_get_contents($kernel->getProjectDir() . '/resources/tests/block.json'), true);

        $block = $blockchain->addBlock(
            $data['action'],
            $data['identifier'],
            $data['author'],
            new \DateTimeImmutable($data['date']),
            $data['metadata']
        );

        $this->assertTrue($blockchain->verifySignature($block));

        self::bootKernel();

        $blocks = $blockchain->getBlocks(1);

        $this->assertTrue($blockchain->verifySignature($blocks[0]));
    }
}
