<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201012150813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added currency to the booking table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE booking ADD currency VARCHAR(3) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE booking DROP currency');
    }
}
