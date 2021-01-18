<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210118164222 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation_car_rental (reservation_id INT NOT NULL, car_rental_id INT NOT NULL, INDEX IDX_6EDBE73BB83297E7 (reservation_id), INDEX IDX_6EDBE73BA02DD105 (car_rental_id), PRIMARY KEY(reservation_id, car_rental_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservation_car_rental ADD CONSTRAINT FK_6EDBE73BB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_car_rental ADD CONSTRAINT FK_6EDBE73BA02DD105 FOREIGN KEY (car_rental_id) REFERENCES car_rental (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation DROP car_rental_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE reservation_car_rental');
        $this->addSql('ALTER TABLE reservation ADD car_rental_id INT NOT NULL');
    }
}
