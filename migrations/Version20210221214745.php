<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210221214745 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE localisation ADD city_id INT NOT NULL');
        $this->addSql('ALTER TABLE localisation ADD CONSTRAINT FK_BFD3CE8F8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('CREATE INDEX IDX_BFD3CE8F8BAC62AF ON localisation (city_id)');
        $this->addSql('ALTER TABLE participation ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_AB55E24FA76ED395 ON participation (user_id)');
        $this->addSql('ALTER TABLE question_user ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE question_user ADD CONSTRAINT FK_D37D3BA6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D37D3BA6A76ED395 ON question_user (user_id)');
        $this->addSql('ALTER TABLE ticket ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_97A0ADA3A76ED395 ON ticket (user_id)');
        $this->addSql('ALTER TABLE user ADD localisation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649C68BE09C FOREIGN KEY (localisation_id) REFERENCES localisation (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649C68BE09C ON user (localisation_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE localisation DROP FOREIGN KEY FK_BFD3CE8F8BAC62AF');
        $this->addSql('DROP INDEX IDX_BFD3CE8F8BAC62AF ON localisation');
        $this->addSql('ALTER TABLE localisation DROP city_id');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24FA76ED395');
        $this->addSql('DROP INDEX IDX_AB55E24FA76ED395 ON participation');
        $this->addSql('ALTER TABLE participation DROP user_id');
        $this->addSql('ALTER TABLE question_user DROP FOREIGN KEY FK_D37D3BA6A76ED395');
        $this->addSql('DROP INDEX IDX_D37D3BA6A76ED395 ON question_user');
        $this->addSql('ALTER TABLE question_user DROP user_id');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3A76ED395');
        $this->addSql('DROP INDEX IDX_97A0ADA3A76ED395 ON ticket');
        $this->addSql('ALTER TABLE ticket DROP user_id');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649C68BE09C');
        $this->addSql('DROP INDEX IDX_8D93D649C68BE09C ON user');
        $this->addSql('ALTER TABLE user DROP localisation_id');
    }
}
