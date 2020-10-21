<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201022160202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added columns `is_extra_night` and `is_extra_room` to the table `booking_date`';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking_date ADD is_extra_night TINYINT(1) NOT NULL, ADD is_extra_room TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking_date DROP is_extra_night, DROP is_extra_room');
    }
}
