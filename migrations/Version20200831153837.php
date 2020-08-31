<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200831153837 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added table for holding manageable components information; Added unique index for component_uuid and date on room_availability table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE flat_manageable_component (box_golden_id VARCHAR(45) NOT NULL, experience_golden_id VARCHAR(45) NOT NULL, component_golden_id VARCHAR(45) NOT NULL, component_uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid_binary_ordered_time)\', duration INT UNSIGNED NOT NULL, is_sellable TINYINT(1) NOT NULL, room_stock_type VARCHAR(10) NOT NULL COMMENT \'(DC2Type:room_stock_type)\', last_bookable_date DATE DEFAULT NULL, INDEX IDX_3C0C00776F37D365 (experience_golden_id), INDEX IDX_3C0C0077C561D977 (component_golden_id), PRIMARY KEY(box_golden_id, experience_golden_id, component_golden_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_89C5BA2C6D5FE790AA9E377A ON room_availability (component_uuid, date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE flat_manageable_component');
        $this->addSql('DROP INDEX UNIQ_89C5BA2C6D5FE790AA9E377A ON room_availability');
    }
}
