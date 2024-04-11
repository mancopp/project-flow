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
        return 'Creates task and status tables';
    }

    public function up(Schema $schema): void
    {
        // Create 'status' table
        $this->addSql('CREATE TABLE status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO status (name) VALUES (\'To Do\'), (\'In Progress\'), (\'Review\'), (\'Done\')');

        // Create 'task' table
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, status_id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_TASK_STATUS FOREIGN KEY (status_id) REFERENCES status (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE status');
    }
}
