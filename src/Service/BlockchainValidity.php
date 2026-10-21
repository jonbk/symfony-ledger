<?php

namespace App\Service;

use App\Entity\Block;

final readonly class BlockchainValidity
{
    public function __construct(
        public int     $checkedBlocks,
        public ?Block  $lastBlock,
        public ?Block  $invalidBlock = null,
        public ?string $error = null,
    )
    {
    }

    public function isValid(): bool
    {
        return $this->error === null;
    }
}
