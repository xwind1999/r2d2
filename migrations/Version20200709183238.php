<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200709183238 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added FKs; added universe column to the box table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE814B63BE FOREIGN KEY (partner_uuid) REFERENCES partner (uuid)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE856F9FBB FOREIGN KEY (experience_uuid) REFERENCES experience (uuid)');
        $this->addSql('ALTER TABLE booking_date ADD CONSTRAINT FK_B20F1534C9EE72E8 FOREIGN KEY (booking_uuid) REFERENCES booking (uuid)');
        $this->addSql('ALTER TABLE booking_date ADD CONSTRAINT FK_B20F15346D5FE790 FOREIGN KEY (component_uuid) REFERENCES component (uuid)');
        $this->addSql('ALTER TABLE box ADD universe VARCHAR(3) DEFAULT NULL');
        $this->addSql('ALTER TABLE box_experience ADD CONSTRAINT FK_BA914AB5271B1CAF FOREIGN KEY (box_uuid) REFERENCES box (uuid)');
        $this->addSql('ALTER TABLE box_experience ADD CONSTRAINT FK_BA914AB5856F9FBB FOREIGN KEY (experience_uuid) REFERENCES experience (uuid)');
        $this->addSql('ALTER TABLE component ADD CONSTRAINT FK_49FEA157814B63BE FOREIGN KEY (partner_uuid) REFERENCES partner (uuid)');
        $this->addSql('ALTER TABLE experience ADD CONSTRAINT FK_590C103814B63BE FOREIGN KEY (partner_uuid) REFERENCES partner (uuid)');
        $this->addSql('ALTER TABLE experience_component ADD CONSTRAINT FK_3D513714856F9FBB FOREIGN KEY (experience_uuid) REFERENCES experience (uuid)');
        $this->addSql('ALTER TABLE experience_component ADD CONSTRAINT FK_3D5137146D5FE790 FOREIGN KEY (component_uuid) REFERENCES component (uuid)');
        $this->addSql('ALTER TABLE guest ADD CONSTRAINT FK_ACB79A35C9EE72E8 FOREIGN KEY (booking_uuid) REFERENCES booking (uuid)');
        $this->addSql('ALTER TABLE room_availability ADD CONSTRAINT FK_89C5BA2C6D5FE790 FOREIGN KEY (component_uuid) REFERENCES component (uuid)');
        $this->addSql('ALTER TABLE room_price ADD CONSTRAINT FK_807483726D5FE790 FOREIGN KEY (component_uuid) REFERENCES component (uuid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE814B63BE');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE856F9FBB');
        $this->addSql('ALTER TABLE booking_date DROP FOREIGN KEY FK_B20F1534C9EE72E8');
        $this->addSql('ALTER TABLE booking_date DROP FOREIGN KEY FK_B20F15346D5FE790');
        $this->addSql('ALTER TABLE box DROP universe');
        $this->addSql('ALTER TABLE box_experience DROP FOREIGN KEY FK_BA914AB5271B1CAF');
        $this->addSql('ALTER TABLE box_experience DROP FOREIGN KEY FK_BA914AB5856F9FBB');
        $this->addSql('ALTER TABLE component DROP FOREIGN KEY FK_49FEA157814B63BE');
        $this->addSql('ALTER TABLE experience DROP FOREIGN KEY FK_590C103814B63BE');
        $this->addSql('ALTER TABLE experience_component DROP FOREIGN KEY FK_3D513714856F9FBB');
        $this->addSql('ALTER TABLE experience_component DROP FOREIGN KEY FK_3D5137146D5FE790');
        $this->addSql('ALTER TABLE guest DROP FOREIGN KEY FK_ACB79A35C9EE72E8');
        $this->addSql('ALTER TABLE room_availability DROP FOREIGN KEY FK_89C5BA2C6D5FE790');
        $this->addSql('ALTER TABLE room_price DROP FOREIGN KEY FK_807483726D5FE790');
    }
}
