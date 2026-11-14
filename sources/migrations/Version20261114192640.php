<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Table du cache applicatif (cache.app -> Doctrine DBAL) : remplace le stockage
 * fichier de var/cache/prod/pools, qui peut saturer le quota d'inodes de
 * l'hébergement mutualisé (un fichier par entrée, jamais supprimé à l'expiration).
 */
final class Version20261114192640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cache applicatif en base (cache_items) au lieu du système de fichiers';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cache_items (item_id VARBINARY(255) NOT NULL, item_data MEDIUMBLOB NOT NULL, item_lifetime INT UNSIGNED DEFAULT NULL, item_time INT UNSIGNED NOT NULL, PRIMARY KEY(item_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE cache_items');
    }
}
