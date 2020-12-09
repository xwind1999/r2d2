<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201208141737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added price and currency columns to the component table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE component ADD price INT UNSIGNED DEFAULT NULL, ADD currency VARCHAR(3) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE component DROP price, DROP currency');
    }
}
