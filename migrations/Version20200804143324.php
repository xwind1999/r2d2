<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200804143324 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adding the column external_updated_at at table room_availability';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE room_availability ADD external_updated_at DATETIME(3) DEFAULT NULL COMMENT \'(DC2Type:datetime_milliseconds)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE room_availability DROP external_updated_at');
    }
}
