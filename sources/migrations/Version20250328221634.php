<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250328221634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE question_user_user (question_user_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_FEC7DE1BA9B20688 (question_user_id), INDEX IDX_FEC7DE1BA76ED395 (user_id), PRIMARY KEY(question_user_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE test_e (id INT AUTO_INCREMENT NOT NULL, content VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message_send_email (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_412055ADFB7336F0 (queue_name), INDEX IDX_412055ADE3BD61CE (available_at), INDEX IDX_412055AD16BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message_failed_send_email (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_68F20528FB7336F0 (queue_name), INDEX IDX_68F20528E3BD61CE (available_at), INDEX IDX_68F2052816BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE question_user_user ADD CONSTRAINT FK_FEC7DE1BA9B20688 FOREIGN KEY (question_user_id) REFERENCES question_user (id)');
        $this->addSql('ALTER TABLE question_user_user ADD CONSTRAINT FK_FEC7DE1BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event CHANGE ended_at ended_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE question_user DROP FOREIGN KEY FK_D37D3BA6A76ED395');
        $this->addSql('DROP INDEX IDX_D37D3BA6A76ED395 ON question_user');
        $this->addSql('ALTER TABLE question_user DROP user_id, CHANGE subject subject VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE transport CHANGE go_started_at go_started_at DATETIME DEFAULT NULL, CHANGE go_ended_at go_ended_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question_user_user DROP FOREIGN KEY FK_FEC7DE1BA9B20688');
        $this->addSql('ALTER TABLE question_user_user DROP FOREIGN KEY FK_FEC7DE1BA76ED395');
        $this->addSql('DROP TABLE question_user_user');
        $this->addSql('DROP TABLE test_e');
        $this->addSql('DROP TABLE message_send_email');
        $this->addSql('DROP TABLE message_failed_send_email');
        $this->addSql('ALTER TABLE event CHANGE ended_at ended_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE question_user ADD user_id INT DEFAULT NULL, CHANGE subject subject VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE question_user ADD CONSTRAINT FK_D37D3BA6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_D37D3BA6A76ED395 ON question_user (user_id)');
        $this->addSql('ALTER TABLE transport CHANGE go_started_at go_started_at DATETIME NOT NULL, CHANGE go_ended_at go_ended_at DATETIME NOT NULL');
    }
}
