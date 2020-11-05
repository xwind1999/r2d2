<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201105141153 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adding box/experience index to the flat manageable component table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_3C0C0077CA72E5156F37D365 ON flat_manageable_component (box_golden_id, experience_golden_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_3C0C0077CA72E5156F37D365 ON flat_manageable_component');
    }
}
