<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\DataFixtures\CalendarFixtureHelper;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201202142554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added calendar table to make our lives easier';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE calendar (date DATE NOT NULL, PRIMARY KEY(date)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $dates = CalendarFixtureHelper::generateDates();

        foreach ($dates as $dateQuery) {
            $this->addSql(...$dateQuery);
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE calendar');
    }
}
