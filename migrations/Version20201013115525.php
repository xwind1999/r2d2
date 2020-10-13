<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201013115525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Changed to not allow NULL values for the columns `voucher`, `brand` and `country` from the table `booking`';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE booking CHANGE voucher voucher VARCHAR(12) NOT NULL, CHANGE brand brand VARCHAR(3) NOT NULL, CHANGE country country VARCHAR(2) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE booking CHANGE voucher voucher VARCHAR(12) DEFAULT NULL, CHANGE brand brand VARCHAR(3) DEFAULT NULL, CHANGE country country VARCHAR(2) DEFAULT NULL');
    }
}
