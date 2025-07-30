<?php

namespace App\Service;

use Throwable;
use Doctrine\DBAL\Connection;

class TablesAlterService
{
        public function alterAllTables(Connection $connection): array
    {
        $messages = [];
        try
        {
            $messages[] = $this->addMaritalStatusToPersons($connection, 'ex11_persons');
            $messages[] = $this->createBankAccountRelation($connection, 'ex11_bank_accounts', 'ex11_persons');
            $messages[] = $this->createAddressRelation($connection, 'ex11_addresses', 'ex11_persons');
            return $messages;
        }
        catch (Throwable $e)
        {
            $messages[] = "danger:Global error, there was a problem in the table creation process : " . $e->getMessage();
            return $messages;
        }
    }
    public function addMaritalStatusToPersons(Connection $connection, string $tableName): string
    {
        $sql = "ALTER TABLE $tableName ADD COLUMN marital_status ENUM('single', 'married', 'widower') DEFAULT 'single'";
        try
        {
			if (!$this->checkTableExistence($connection, $tableName))
				return "danger: Error, the table $tableName does not exist.";
            // Vérifie si la colonne existe déjà pour éviter les erreurs
			if ($this->doesColumnExist($connection, $tableName, 'marital_status'))
				return "info:The column marital_status already exists in table $tableName.";
            $connection->executeStatement($sql);
            return "success:Success! The column marital_status was added to table $tableName.";
        }
        catch (Throwable $e)
        {
            return "danger:Error while altering table $tableName: " . $e->getMessage();
        }
    }

    public function createBankAccountRelation(Connection $connection, string $tableName, string $personTable): string
    {
        try
        {
            // 1. Vérifier existence des deux tables
            if (!$this->checkTableExistence($connection, $tableName))
                return "danger: Error, the table $tableName does not exist.";
            if (!$this->checkTableExistence($connection, $personTable))
                return "danger: Error, the table $personTable does not exist.";

            // 2. Vérifier colonne person_id
            if (!$this->doesColumnExist($connection, $tableName, 'person_id'))
            {
                // Ajoute colonne et contrainte en même temps
                $connection->executeStatement("
                    ALTER TABLE $tableName
                    ADD COLUMN person_id INT UNIQUE,
                    ADD CONSTRAINT fk_{$tableName}_person FOREIGN KEY (person_id) REFERENCES $personTable(id) ON DELETE CASCADE
                ");
                return "success:Success! Relation one-to-one $tableName/$personTable created.";
            }
            // Colonne existe, vérifier si la contrainte existe
            $constraints = $connection->fetchAllAssociative(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = '$tableName' AND COLUMN_NAME = 'person_id' AND REFERENCED_TABLE_NAME = '$personTable'"
            );
            if (!empty($constraints))
                return "info:The relation between $tableName and $personTable already exists.";
            // Colonne existe, contrainte pas encore ajoutée
            $connection->executeStatement("
                ALTER TABLE $tableName
                ADD CONSTRAINT fk_{$tableName}_person FOREIGN KEY (person_id) REFERENCES $personTable(id) ON DELETE CASCADE
            ");
            return "success:Success! Foreign key for $tableName/$personTable added.";
        }
        catch (Throwable $e)
        {
            return "danger:Error while creating $tableName relation: " . $e->getMessage();
        }
    }



    public function createAddressRelation(Connection $connection, string $addressTable, string $personTable): string
    {
        try
        {
            // 1. Vérifier existence des deux tables
            if (!$this->checkTableExistence($connection, $addressTable))
                return "danger: Error, the table $addressTable does not exist.";
            if (!$this->checkTableExistence($connection, $personTable))
                return "danger: Error, the table $personTable does not exist.";

            // 2. Vérifier colonne person_id
            if (!$this->doesColumnExist($connection, $addressTable, 'person_id'))
            {
                // Ajoute colonne et contrainte en même temps
                $connection->executeStatement("
                    ALTER TABLE $addressTable
                    ADD COLUMN person_id INT,
                    ADD CONSTRAINT fk_{$addressTable}_person FOREIGN KEY (person_id) REFERENCES $personTable(id) ON DELETE CASCADE
                ");
                return "success:Success! Relation one-to-many $addressTable/$personTable created.";
            }
            // Colonne existe, vérifier si la contrainte existe
            $constraints = $connection->fetchAllAssociative(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = '$addressTable' AND COLUMN_NAME = 'person_id' AND REFERENCED_TABLE_NAME = '$personTable'"
            );
            if (!empty($constraints))
                return "info:The relation between $addressTable and $personTable already exists.";
            // Colonne existe, contrainte pas encore ajoutée
            $connection->executeStatement("
                ALTER TABLE $addressTable
                ADD CONSTRAINT fk_{$addressTable}_person FOREIGN KEY (person_id) REFERENCES $personTable(id) ON DELETE CASCADE
            ");
            return "success:Success! Foreign key for $addressTable/$personTable added.";
        }
        catch (Throwable $e)
        {
            return "danger:Error while creating $addressTable relation: " . $e->getMessage();
        }
    }



	public function doesColumnExist(Connection $connection, string $tableName, string $columnName): bool
	{
		try
		{
			$columns = $connection->fetchAllAssociative("SHOW COLUMNS FROM $tableName LIKE :column", ['column' => $columnName]);
			return count($columns) > 0;
		}
		catch (Throwable $e)
		{
			return false;
		}
	}

	private function checkTableExistence(Connection $connection, string $tableName): bool
    {
        try
        {
            $result = $connection->fetchOne("SHOW TABLES LIKE '$tableName'");
            return ($result !== false);
        }
        catch (Throwable $e)
        {
            return false;
        }
    }
}
?>
