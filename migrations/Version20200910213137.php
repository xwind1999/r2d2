<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200910213137 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Setting partner golden id as not nullable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE flat_manageable_component CHANGE partner_golden_id partner_golden_id VARCHAR(45) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE flat_manageable_component CHANGE partner_golden_id partner_golden_id VARCHAR(45) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
