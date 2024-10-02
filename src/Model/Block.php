<?php

namespace App\Model;

use Ramsey\Uuid\Rfc4122\UuidV4;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class Block
{
    private UuidInterface $uuid;
    private \DateTimeImmutable $timestamp;
    private string $action;
    private string $identifier;
    private string $author;
    private \DateTimeImmutable $date;
    private array $metadata;
    private ?string $previousSignature;
    private ?string $signature;

    public function __construct(
        string             $action,
        string             $identifier,
        string             $author,
        \DateTimeImmutable $date,
        array              $metadata,
        ?string            $previousSignature
    )
    {
        $this->uuid = Uuid::uuid4();
        $this->timestamp = new \DateTimeImmutable();
        $this->action = $action;
        $this->identifier = $identifier;
        $this->author = $author;
        $this->date = $date;
        $this->metadata = $metadata;
        $this->previousSignature = $previousSignature;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getPreviousSignature(): ?string
    {
        return $this->previousSignature;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function setSignature(string $signature): self
    {
        $this->signature = $signature;

        return $this;
    }

    public function toSign(): string
    {
        return json_encode([
            'uuid' => $this->uuid->toString(),
            'timestamp' => $this->timestamp->format('Y-m-d\TH:i:s.v\Z'),
            'action' => $this->action,
            'identifier' => $this->identifier,
            'author' => $this->author,
            'date' => $this->date->format('Y-m-d\TH:i:s.v\Z'),
            'metadata' => $this->metadata,
            'previousSignature' => $this->previousSignature
        ]);
    }
}