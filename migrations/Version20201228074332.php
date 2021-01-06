<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201228074332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added column availability_type to booking table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE booking ADD availability_type VARCHAR(10) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE booking DROP availability_type');
    }
}
