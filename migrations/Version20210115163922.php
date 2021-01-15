<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210115163922 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE car_rental ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE car_rental ADD CONSTRAINT FK_E712E8F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_E712E8F7E3C61F9 ON car_rental (owner_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE car_rental DROP FOREIGN KEY FK_E712E8F7E3C61F9');
        $this->addSql('DROP INDEX IDX_E712E8F7E3C61F9 ON car_rental');
        $this->addSql('ALTER TABLE car_rental DROP owner_id');
    }
}
