<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715155236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make all numeric fields nullable in i_o table';
    }

    public function up(Schema $schema): void
    {
        // Make all numeric fields nullable
        $this->addSql('ALTER TABLE i_o MODIFY section FLOAT DEFAULT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY crimping_height FLOAT DEFAULT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY insulation_height FLOAT DEFAULT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY crimping_width FLOAT DEFAULT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY insulation_width FLOAT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Revert changes - make fields required again
        $this->addSql('ALTER TABLE i_o MODIFY section FLOAT NOT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY crimping_height FLOAT NOT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY insulation_height FLOAT NOT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY crimping_width FLOAT NOT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY insulation_width FLOAT NOT NULL');
    }
} 