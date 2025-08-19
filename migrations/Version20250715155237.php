<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715155237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create complaint table';
    }

    public function up(Schema $schema): void
    {
        // Create complaint table
        $this->addSql('CREATE TABLE complaint (
            id INT AUTO_INCREMENT NOT NULL,
            user_id VARCHAR(255) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            description LONGTEXT NOT NULL,
            status VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            admin_response LONGTEXT DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_5F2732B5A76ED395 (user_id),
            CONSTRAINT FK_5F2732B5A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // Drop complaint table
        $this->addSql('DROP TABLE complaint');
    }
} 