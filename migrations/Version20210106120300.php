<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210106120300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added column last_status_channel to booking table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE booking ADD last_status_channel VARCHAR(15) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE booking DROP last_status_channel');
    }
}
