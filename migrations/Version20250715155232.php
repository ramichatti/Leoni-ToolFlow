<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715155232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add conformite column to i_o table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE i_o ADD conformite VARCHAR(20) NOT NULL DEFAULT "non conforme"');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE i_o DROP COLUMN conformite');
    }
}
