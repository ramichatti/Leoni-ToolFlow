<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240615000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change role column to use enum values';
    }

    public function up(Schema $schema): void
    {
        // Create a temporary column for the enum
        $this->addSql('ALTER TABLE `user` ADD role_enum VARCHAR(255) NOT NULL');
        
        // Copy data from old column to new column
        $this->addSql('UPDATE `user` SET role_enum = role');
        
        // Drop the old column
        $this->addSql('ALTER TABLE `user` DROP role');
        
        // Rename the new column to the original name
        $this->addSql('ALTER TABLE `user` CHANGE role_enum role VARCHAR(255) NOT NULL COMMENT \'(DC2Type:App\\\\Enum\\\\Role)\'');
    }

    public function down(Schema $schema): void
    {
        // Create a temporary column without the enum type
        $this->addSql('ALTER TABLE `user` ADD role_temp VARCHAR(20) NOT NULL');
        
        // Copy data from enum column to standard column
        $this->addSql('UPDATE `user` SET role_temp = role');
        
        // Drop the enum column
        $this->addSql('ALTER TABLE `user` DROP role');
        
        // Rename the standard column to the original name
        $this->addSql('ALTER TABLE `user` CHANGE role_temp role VARCHAR(20) NOT NULL');
    }
} 