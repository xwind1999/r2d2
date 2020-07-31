<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200730134106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Updated all external_updated_at fields to DATETIME(3), so we can store the milliseconds';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE box CHANGE external_updated_at external_updated_at DATETIME(3) DEFAULT NULL COMMENT \'(DC2Type:datetime_milliseconds)\'');
        $this->addSql('ALTER TABLE box_experience CHANGE external_updated_at external_updated_at DATETIME(3) DEFAULT NULL COMMENT \'(DC2Type:datetime_milliseconds)\'');
        $this->addSql('ALTER TABLE component CHANGE external_updated_at external_updated_at DATETIME(3) DEFAULT NULL COMMENT \'(DC2Type:datetime_milliseconds)\'');
        $this->addSql('ALTER TABLE experience CHANGE external_updated_at external_updated_at DATETIME(3) DEFAULT NULL COMMENT \'(DC2Type:datetime_milliseconds)\', CHANGE price_updated_at price_updated_at DATETIME(3) DEFAULT NULL COMMENT \'(DC2Type:datetime_milliseconds)\'');
        $this->addSql('ALTER TABLE experience_component CHANGE external_updated_at external_updated_at DATETIME(3) DEFAULT NULL COMMENT \'(DC2Type:datetime_milliseconds)\'');
        $this->addSql('ALTER TABLE partner CHANGE status status VARCHAR(16) NOT NULL COMMENT \'(DC2Type:partner_status)\', CHANGE external_updated_at external_updated_at DATETIME(3) DEFAULT NULL COMMENT \'(DC2Type:datetime_milliseconds)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE box CHANGE external_updated_at external_updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE box_experience CHANGE external_updated_at external_updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE component CHANGE external_updated_at external_updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE experience CHANGE external_updated_at external_updated_at DATETIME DEFAULT NULL, CHANGE price_updated_at price_updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE experience_component CHANGE external_updated_at external_updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE partner CHANGE status status VARCHAR(16) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE external_updated_at external_updated_at DATETIME DEFAULT NULL');
    }
}
