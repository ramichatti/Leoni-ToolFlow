<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Remove created_by_id column from measure table
 */
final class Version20240620000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove created_by_id column from measure table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE measure DROP FOREIGN KEY FK_80071925B03A8386');
        $this->addSql('ALTER TABLE measure DROP created_by_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE measure ADD created_by_id INT NOT NULL');
        $this->addSql('ALTER TABLE measure ADD CONSTRAINT FK_80071925B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
    }
} 