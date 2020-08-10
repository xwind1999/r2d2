<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200810100619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added external_updated_at to the room_price table, added composite indexes to room_price and room_availability';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_89C5BA2CC561D977AA9E377A ON room_availability (component_golden_id, date)');
        $this->addSql('ALTER TABLE room_price ADD external_updated_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_80748372C561D977AA9E377A ON room_price (component_golden_id, date)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_89C5BA2CC561D977AA9E377A ON room_availability');
        $this->addSql('DROP INDEX UNIQ_80748372C561D977AA9E377A ON room_price');
        $this->addSql('ALTER TABLE room_price DROP external_updated_at');
    }
}
