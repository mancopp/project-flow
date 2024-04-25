<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240409165931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates task and status tables, including default statuses as immutable.';
    }

    public function up(Schema $schema): void
    {
        // Create 'status' table with 'is_default' column included
        $this->addSql('CREATE TABLE status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, is_default BOOLEAN NOT NULL DEFAULT FALSE, PRIMARY KEY(id))');
        // Insert default statuses and mark them as immutable
        $this->addSql("INSERT INTO status (name, is_default) VALUES ('To Do', TRUE), ('In Progress', TRUE), ('Review', TRUE), ('Done', TRUE)");

        // Create 'task' table
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, status_id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_TASK_STATUS FOREIGN KEY (status_id) REFERENCES status (id)');
    }

    public function down(Schema $schema): void
    {
        // Drop 'task' and 'status' tables when migrating down
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE status');
    }
}