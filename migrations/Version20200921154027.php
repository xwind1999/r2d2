<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200921154027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added missing index for component_uuid,date on room_price';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_807483726D5FE790AA9E377A ON room_price (component_uuid, date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_807483726D5FE790AA9E377A ON room_price');
    }
}
