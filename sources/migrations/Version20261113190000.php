<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * QuestionUser#user : ManyToMany (table question_user_user) -> ManyToOne nullable (question_user.user_id).
 * Le mapping ManyToMany était incohérent avec la propriété ?User et faisait échouer le flush.
 * Les auteurs existants sont repris depuis la table de jointure avant sa suppression.
 */
final class Version20261113190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'QuestionUser : auteur en ManyToOne nullable (reprise de question_user_user)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE question_user ADD user_id INT DEFAULT NULL');
        $this->addSql('UPDATE question_user q INNER JOIN (SELECT question_user_id, MIN(user_id) AS user_id FROM question_user_user GROUP BY question_user_id) j ON j.question_user_id = q.id SET q.user_id = j.user_id');
        $this->addSql('ALTER TABLE question_user ADD CONSTRAINT FK_D37D3BA6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_D37D3BA6A76ED395 ON question_user (user_id)');
        $this->addSql('ALTER TABLE question_user_user DROP FOREIGN KEY FK_FEC7DE1BA9B20688');
        $this->addSql('ALTER TABLE question_user_user DROP FOREIGN KEY FK_FEC7DE1BA76ED395');
        $this->addSql('DROP TABLE question_user_user');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE question_user_user (question_user_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_FEC7DE1BA9B20688 (question_user_id), INDEX IDX_FEC7DE1BA76ED395 (user_id), PRIMARY KEY(question_user_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT INTO question_user_user (question_user_id, user_id) SELECT id, user_id FROM question_user WHERE user_id IS NOT NULL');
        $this->addSql('ALTER TABLE question_user_user ADD CONSTRAINT FK_FEC7DE1BA9B20688 FOREIGN KEY (question_user_id) REFERENCES question_user (id)');
        $this->addSql('ALTER TABLE question_user_user ADD CONSTRAINT FK_FEC7DE1BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE question_user DROP FOREIGN KEY FK_D37D3BA6A76ED395');
        $this->addSql('DROP INDEX IDX_D37D3BA6A76ED395 ON question_user');
        $this->addSql('ALTER TABLE question_user DROP user_id');
    }
}
