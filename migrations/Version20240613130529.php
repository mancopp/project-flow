<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240613130529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_57698A6A5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE project_participant ADD role_id INT DEFAULT NULL, DROP role');
        $this->addSql('ALTER TABLE project_participant ADD CONSTRAINT FK_1F509CEAD60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('CREATE INDEX IDX_1F509CEAD60322AC ON project_participant (role_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_participant DROP FOREIGN KEY FK_1F509CEAD60322AC');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP INDEX IDX_1F509CEAD60322AC ON project_participant');
        $this->addSql('ALTER TABLE project_participant ADD role VARCHAR(255) NOT NULL, DROP role_id');
    }
}
