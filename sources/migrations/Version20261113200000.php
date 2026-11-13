<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * localisation.city_id : nullable comme dans le mapping (Localisation#city, JoinColumn nullable).
 * Les migrations historiques la créaient NOT NULL : une base montée depuis les migrations
 * (CI, nouvelle installation) refusait toute localisation sans ville.
 */
final class Version20261113200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'localisation.city_id nullable (alignement sur le mapping)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE localisation CHANGE city_id city_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE localisation CHANGE city_id city_id INT NOT NULL');
    }
}
