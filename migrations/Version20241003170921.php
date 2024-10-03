<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241003170921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE block (uuid UUID NOT NULL, timestamp DATE NOT NULL, action VARCHAR(255) NOT NULL, identifier VARCHAR(255) NOT NULL, author VARCHAR(255) NOT NULL, date DATE NOT NULL, metadata JSON NOT NULL, previous_signature VARCHAR(1024) DEFAULT NULL, signature VARCHAR(1024) NOT NULL, PRIMARY KEY(uuid))');
        $this->addSql('CREATE INDEX IDX_831B9722A5D6E63E ON block (timestamp)');
        $this->addSql('CREATE INDEX IDX_831B972247CC8C92 ON block (action)');
        $this->addSql('CREATE INDEX IDX_831B9722772E836A ON block (identifier)');
        $this->addSql('COMMENT ON COLUMN block.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN block.timestamp IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN block.date IS \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE block');
    }
}
