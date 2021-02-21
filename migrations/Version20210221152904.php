<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210221152904 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event_social_network_link (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, social_network_id INT DEFAULT NULL, link VARCHAR(255) NOT NULL, INDEX IDX_660A842C71F7E88B (event_id), INDEX IDX_660A842CFA413953 (social_network_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event_social_network_link ADD CONSTRAINT FK_660A842C71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE event_social_network_link ADD CONSTRAINT FK_660A842CFA413953 FOREIGN KEY (social_network_id) REFERENCES social_network (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE event_social_network_link');
    }
}
