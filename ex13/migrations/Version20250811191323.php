<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250811191323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ex13_employees (id INT AUTO_INCREMENT NOT NULL, firstname VARCHAR(60) NOT NULL, lastname VARCHAR(60) NOT NULL, email VARCHAR(100) NOT NULL, birthdate DATETIME NOT NULL, active TINYINT(1) NOT NULL, employed_since DATETIME NOT NULL, employed_until DATETIME NOT NULL, hours VARCHAR(255) NOT NULL, salary INT NOT NULL, position VARCHAR(255) NOT NULL, manager_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_13A26A77E7927C74 (email), INDEX IDX_13A26A77783E3463 (manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ex13_employees ADD CONSTRAINT FK_13A26A77783E3463 FOREIGN KEY (manager_id) REFERENCES ex13_employees (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE employee DROP FOREIGN KEY FK_5D9F75A1783E3463');
        $this->addSql('DROP TABLE employee');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE employee (id INT AUTO_INCREMENT NOT NULL, firstname VARCHAR(60) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, lastname VARCHAR(60) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, birthdate DATETIME NOT NULL, active TINYINT(1) NOT NULL, employed_since DATETIME NOT NULL, employed_until DATETIME NOT NULL, hours VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, salary INT NOT NULL, position VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, manager_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_5D9F75A1E7927C74 (email), INDEX IDX_5D9F75A1783E3463 (manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A1783E3463 FOREIGN KEY (manager_id) REFERENCES employee (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ex13_employees DROP FOREIGN KEY FK_13A26A77783E3463');
        $this->addSql('DROP TABLE ex13_employees');
    }
}
