<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200904145126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add partner_golden_id to the flat manageable component table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE flat_manageable_component ADD partner_golden_id VARCHAR(45) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE flat_manageable_component DROP partner_golden_id');
    }
}
