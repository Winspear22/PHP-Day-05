<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250731151720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ex07_users (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, enable TINYINT(1) NOT NULL, birthdate DATETIME NOT NULL, address LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_584FE6F8F85E0677 (username), UNIQUE INDEX UNIQ_584FE6F8E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP TABLE ex05_users');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ex05_users (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, enable TINYINT(1) NOT NULL, birthdate DATETIME NOT NULL, address LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_CFD0F7D1E7927C74 (email), UNIQUE INDEX UNIQ_CFD0F7D1F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE ex07_users');
    }
}
