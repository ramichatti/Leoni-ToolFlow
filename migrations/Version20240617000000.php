<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240617000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Tool entity with ManyToOne relationship to User';
    }

    public function up(Schema $schema): void
    {
        // Create tool table
        $this->addSql('CREATE TABLE tool (
            id VARCHAR(255) NOT NULL,
            created_by_id VARCHAR(255) NOT NULL,
            description VARCHAR(255) NOT NULL,
            manufacturer VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX IDX_20F33ED1B03A8386 (created_by_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Add foreign key constraint
        $this->addSql('ALTER TABLE tool ADD CONSTRAINT FK_20F33ED1B03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key constraint
        $this->addSql('ALTER TABLE tool DROP FOREIGN KEY FK_20F33ED1B03A8386');
        
        // Drop tool table
        $this->addSql('DROP TABLE tool');
    }
} 