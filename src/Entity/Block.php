<?php

namespace App\Entity;

use App\Doctrine\Type\DateTimeImmutableMsType;
use App\Repository\BlockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: BlockRepository::class)]
#[ORM\Index(columns: ['timestamp'])]
#[ORM\Index(columns: ['action'])]
#[ORM\Index(columns: ['identifier'])]
class Block implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $uuid;

    #[ORM\Column(type: DateTimeImmutableMsType::NAME)]
    private \DateTimeImmutable $timestamp;

    #[ORM\Column(type: Types::STRING)]
    private string $action;

    #[ORM\Column(type: Types::STRING)]
    private string $identifier;

    #[ORM\Column(type: Types::STRING)]
    private string $author;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $date;

    #[ORM\Column(type: Types::JSON)]
    private array $metadata;

    #[ORM\Column(type: Types::STRING, length: 1024, nullable: true)]
    private ?string $previousSignature;

    #[ORM\Column(type: Types::STRING, length: 1024)]
    private ?string $signature = null;

    private ?bool $verified = null;

    public function __construct(
        string             $action,
        string             $identifier,
        string             $author,
        \DateTimeImmutable $date,
        array              $metadata,
        ?string            $previousSignature
    )
    {
        $this->uuid = Uuid::v4();
        $this->timestamp = \DateTimeImmutable::createFromFormat('U.u', sprintf('%.6F', microtime(true)));
        $this->action = $action;
        $this->identifier = $identifier;
        $this->author = $author;
        $this->date = $date;
        $this->metadata = $metadata;
        $this->previousSignature = $previousSignature;
    }

    public function getUuid(): Uuid
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

    public function setSignatureVerified(bool $verified): self
    {
        $this->verified = $verified;

        return $this;
    }

    public function payloadToSign(): string
    {
        return json_encode([
            'uuid' => $this->uuid->toString(),
            'timestamp' => $this->timestamp->format(DATE_ATOM),
            'action' => $this->action,
            'identifier' => $this->identifier,
            'author' => $this->author,
            'date' => $this->date->format(DATE_ATOM),
            'metadata' => $this->metadata,
            'previousSignature' => $this->previousSignature
        ]);
    }

    public function jsonSerialize(): array
    {
        return [
            'uuid' => $this->uuid->toString(),
            'timestamp' => $this->timestamp->format('Y-m-d\TH:i:s.uP'),
            'action' => $this->action,
            'identifier' => $this->identifier,
            'author' => $this->author,
            'date' => $this->date->format(DATE_ATOM),
            'metadata' => $this->metadata,
            'previousSignature' => $this->previousSignature,
            'signature' => $this->signature,
            'verified' => $this->verified
        ];
    }
}