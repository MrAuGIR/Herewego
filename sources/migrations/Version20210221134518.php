<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210221134518 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD event_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7B8B83097 FOREIGN KEY (event_group_id) REFERENCES event_group (id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7B8B83097 ON event (event_group_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7B8B83097');
        $this->addSql('DROP INDEX IDX_3BAE0AA7B8B83097 ON event');
        $this->addSql('ALTER TABLE event DROP event_group_id');
    }
}
