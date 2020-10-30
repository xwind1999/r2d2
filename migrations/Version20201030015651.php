<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201030015651 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added indexes to booking and booking_date tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_E00CEDDE6F37D365 ON booking (experience_golden_id)');
        $this->addSql('CREATE INDEX IDX_B20F15346D5FE790AA9E377A ON booking_date (component_uuid, date)');
        $this->addSql('CREATE INDEX IDX_B20F1534C561D977AA9E377A ON booking_date (component_golden_id, date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_E00CEDDE6F37D365 ON booking');
        $this->addSql('DROP INDEX IDX_B20F15346D5FE790AA9E377A ON booking_date');
        $this->addSql('DROP INDEX IDX_B20F1534C561D977AA9E377A ON booking_date');
    }
}
