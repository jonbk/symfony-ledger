<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261018120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'DBAL 4 : domaine timestamp_ms pour le type datetime_immutable_ms et suppression des commentaires DC2Type';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE DOMAIN timestamp_ms AS TIMESTAMP(6)');
        $this->addSql('ALTER TABLE block ALTER timestamp TYPE timestamp_ms');
        $this->addSql('COMMENT ON COLUMN block.uuid IS \'\'');
        $this->addSql('COMMENT ON COLUMN block.timestamp IS \'\'');
        $this->addSql('COMMENT ON COLUMN block.date IS \'\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE block ALTER timestamp TYPE TIMESTAMP(6)');
        $this->addSql('DROP DOMAIN timestamp_ms');
        $this->addSql('COMMENT ON COLUMN block.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN block.timestamp IS \'(DC2Type:datetime_immutable_ms)\'');
        $this->addSql('COMMENT ON COLUMN block.date IS \'(DC2Type:datetime_immutable)\'');
    }
}
