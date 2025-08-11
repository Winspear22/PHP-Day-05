<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250811190620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE employee (id INT AUTO_INCREMENT NOT NULL, firstname VARCHAR(60) NOT NULL, lastname VARCHAR(60) NOT NULL, email VARCHAR(100) NOT NULL, birthdate DATETIME NOT NULL, active TINYINT(1) NOT NULL, employed_since DATETIME NOT NULL, employed_until DATETIME NOT NULL, hours VARCHAR(255) NOT NULL, salary INT NOT NULL, position VARCHAR(255) NOT NULL, manager_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_5D9F75A1E7927C74 (email), INDEX IDX_5D9F75A1783E3463 (manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A1783E3463 FOREIGN KEY (manager_id) REFERENCES employee (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ex12_addresses DROP FOREIGN KEY FK_76403A1217BBB47');
        $this->addSql('ALTER TABLE ex12_bank_accounts DROP FOREIGN KEY FK_58BC9EE7217BBB47');
        $this->addSql('DROP TABLE ex12_addresses');
        $this->addSql('DROP TABLE ex12_bank_accounts');
        $this->addSql('DROP TABLE ex12_persons');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ex12_addresses (id INT AUTO_INCREMENT NOT NULL, address LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, person_id INT NOT NULL, INDEX IDX_76403A1217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ex12_bank_accounts (id INT AUTO_INCREMENT NOT NULL, iban VARCHAR(34) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, bank_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, person_id INT NOT NULL, UNIQUE INDEX UNIQ_58BC9EE7217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ex12_persons (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, enabled TINYINT(1) NOT NULL, birthdate DATETIME NOT NULL, UNIQUE INDEX UNIQ_BB059654F85E0677 (username), UNIQUE INDEX UNIQ_BB059654E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE ex12_addresses ADD CONSTRAINT FK_76403A1217BBB47 FOREIGN KEY (person_id) REFERENCES ex12_persons (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE ex12_bank_accounts ADD CONSTRAINT FK_58BC9EE7217BBB47 FOREIGN KEY (person_id) REFERENCES ex12_persons (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE employee DROP FOREIGN KEY FK_5D9F75A1783E3463');
        $this->addSql('DROP TABLE employee');
    }
}
