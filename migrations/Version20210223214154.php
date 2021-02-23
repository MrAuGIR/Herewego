<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210223214154 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transport ADD localisation_start_id INT DEFAULT NULL, ADD localisation_return_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transport ADD CONSTRAINT FK_66AB212EEB8A9E18 FOREIGN KEY (localisation_start_id) REFERENCES localisation (id)');
        $this->addSql('ALTER TABLE transport ADD CONSTRAINT FK_66AB212E564C7328 FOREIGN KEY (localisation_return_id) REFERENCES localisation (id)');
        $this->addSql('CREATE INDEX IDX_66AB212EEB8A9E18 ON transport (localisation_start_id)');
        $this->addSql('CREATE INDEX IDX_66AB212E564C7328 ON transport (localisation_return_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transport DROP FOREIGN KEY FK_66AB212EEB8A9E18');
        $this->addSql('ALTER TABLE transport DROP FOREIGN KEY FK_66AB212E564C7328');
        $this->addSql('DROP INDEX IDX_66AB212EEB8A9E18 ON transport');
        $this->addSql('DROP INDEX IDX_66AB212E564C7328 ON transport');
        $this->addSql('ALTER TABLE transport DROP localisation_start_id, DROP localisation_return_id');
    }
}
