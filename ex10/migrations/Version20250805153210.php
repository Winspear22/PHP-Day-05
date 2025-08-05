<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250805153210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ex10_orm_records (id INT AUTO_INCREMENT NOT NULL, data VARCHAR(255) NOT NULL, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ex09_addresses DROP FOREIGN KEY FK_BFE0C88A217BBB47');
        $this->addSql('ALTER TABLE ex09_bank_accounts DROP FOREIGN KEY FK_2820A988217BBB47');
        $this->addSql('DROP TABLE ex09_addresses');
        $this->addSql('DROP TABLE ex09_bank_accounts');
        $this->addSql('DROP TABLE ex09_persons');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ex09_addresses (id INT AUTO_INCREMENT NOT NULL, address LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, person_id INT NOT NULL, INDEX IDX_BFE0C88A217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ex09_bank_accounts (id INT AUTO_INCREMENT NOT NULL, iban VARCHAR(34) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, bank_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, person_id INT NOT NULL, UNIQUE INDEX UNIQ_2820A988217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ex09_persons (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, enabled TINYINT(1) NOT NULL, birthdate DATETIME NOT NULL, UNIQUE INDEX UNIQ_D69263B7E7927C74 (email), UNIQUE INDEX UNIQ_D69263B7F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE ex09_addresses ADD CONSTRAINT FK_BFE0C88A217BBB47 FOREIGN KEY (person_id) REFERENCES ex09_persons (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE ex09_bank_accounts ADD CONSTRAINT FK_2820A988217BBB47 FOREIGN KEY (person_id) REFERENCES ex09_persons (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('DROP TABLE ex10_orm_records');
    }
}
