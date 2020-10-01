<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200930143457 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop is_channel_manager_active flag';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE partner DROP is_channel_manager_active');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE partner ADD is_channel_manager_active TINYINT(1) NOT NULL');
    }
}
