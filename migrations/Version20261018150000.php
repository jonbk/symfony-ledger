<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261018150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Unicité du timestamp des blocs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_831B9722A5D6E63E');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_831B9722A5D6E63E ON block (timestamp)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_831B9722A5D6E63E');
        $this->addSql('CREATE INDEX IDX_831B9722A5D6E63E ON block (timestamp)');
    }
}
