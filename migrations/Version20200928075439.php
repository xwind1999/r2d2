<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200928075439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop room_availability type column in favour of component/flat_manageable_component room_stock_type';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE room_availability DROP type');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE room_availability ADD type VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
