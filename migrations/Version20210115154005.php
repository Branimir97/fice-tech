<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210115154005 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE image ADD base64 LONGTEXT NOT NULL, DROP path');
        $this->addSql('ALTER TABLE vehicle ADD car_rental_id INT NOT NULL, ADD fuel_type VARCHAR(255) NOT NULL, ADD gate_number INT NOT NULL, ADD discount DOUBLE PRECISION DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E486A02DD105 FOREIGN KEY (car_rental_id) REFERENCES car_rental (id)');
        $this->addSql('CREATE INDEX IDX_1B80E486A02DD105 ON vehicle (car_rental_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE image ADD path VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP base64');
        $this->addSql('ALTER TABLE vehicle DROP FOREIGN KEY FK_1B80E486A02DD105');
        $this->addSql('DROP INDEX IDX_1B80E486A02DD105 ON vehicle');
        $this->addSql('ALTER TABLE vehicle DROP car_rental_id, DROP fuel_type, DROP gate_number, DROP discount, DROP created_at, DROP updated_at');
    }
}
