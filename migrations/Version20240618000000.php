<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240618000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add technical attributes to Tool entity';
    }

    public function up(Schema $schema): void
    {
        // Add new columns to tool table
        $this->addSql('ALTER TABLE tool ADD section DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE tool ADD crimping_height DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE tool ADD insulation_height DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE tool ADD crimping_width DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE tool ADD insulation_width DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Drop new columns from tool table
        $this->addSql('ALTER TABLE tool DROP section');
        $this->addSql('ALTER TABLE tool DROP crimping_height');
        $this->addSql('ALTER TABLE tool DROP insulation_height');
        $this->addSql('ALTER TABLE tool DROP crimping_width');
        $this->addSql('ALTER TABLE tool DROP insulation_width');
    }
} 