<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240619000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make technical attributes required (not null) in Tool entity';
    }

    public function up(Schema $schema): void
    {
        // Make technical attributes required (not null)
        $this->addSql('ALTER TABLE tool CHANGE section section DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE tool CHANGE crimping_height crimping_height DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE tool CHANGE insulation_height insulation_height DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE tool CHANGE crimping_width crimping_width DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE tool CHANGE insulation_width insulation_width DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Make technical attributes optional (nullable)
        $this->addSql('ALTER TABLE tool CHANGE section section DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE tool CHANGE crimping_height crimping_height DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE tool CHANGE insulation_height insulation_height DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE tool CHANGE crimping_width crimping_width DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE tool CHANGE insulation_width insulation_width DOUBLE PRECISION DEFAULT NULL');
    }
} 