<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715155233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add machine column to i_o table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE i_o ADD machine INT(11) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE i_o DROP COLUMN machine');
    }
} 