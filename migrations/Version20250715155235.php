<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715155235 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow null values for numeric fields and make measure_id nullable in i_o table';
    }

    public function up(Schema $schema): void
    {
        // First check if with_cahier column exists, if not add it
        $columns = $this->connection->fetchAllAssociative('SHOW COLUMNS FROM i_o');
        $withCahierExists = false;
        foreach ($columns as $column) {
            if ($column['Field'] === 'with_cahier') {
                $withCahierExists = true;
                break;
            }
        }
        
        if (!$withCahierExists) {
            $this->addSql('ALTER TABLE i_o ADD with_cahier VARCHAR(3) DEFAULT "no"');
        }
        
        // Update other columns
        $this->addSql('ALTER TABLE i_o MODIFY measure_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY conformite VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY section FLOAT DEFAULT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY crimping_height FLOAT DEFAULT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY insulation_height FLOAT DEFAULT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY crimping_width FLOAT DEFAULT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY insulation_width FLOAT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE i_o MODIFY measure_id INT NOT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY conformite VARCHAR(20) NOT NULL DEFAULT "non conforme"');
        $this->addSql('ALTER TABLE i_o MODIFY section FLOAT NOT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY crimping_height FLOAT NOT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY insulation_height FLOAT NOT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY crimping_width FLOAT NOT NULL');
        $this->addSql('ALTER TABLE i_o MODIFY insulation_width FLOAT NOT NULL');
    }
} 