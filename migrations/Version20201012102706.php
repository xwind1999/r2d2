<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201012102706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added currency to the experience table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE experience ADD currency VARCHAR(3) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE experience DROP currency');
    }
}
