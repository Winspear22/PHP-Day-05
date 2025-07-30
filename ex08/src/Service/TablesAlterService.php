<?php

namespace App\Service;

use Throwable;
use Doctrine\DBAL\Connection;

class TablesAlterService
{
    public function addMaritalStatusToPersons(Connection $connection, string $tableName): string
    {
        $sql = "ALTER TABLE $tableName ADD COLUMN marital_status ENUM('single', 'married', 'widower') DEFAULT 'single'";
        try
        {
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

	public function createBankAccountRelation(Connection $connection): string
	{
		try
		{
			// Vérifie d'abord si la colonne person_id existe dans bank_accounts
			if (!$this->doesColumnExist($connection, 'bank_accounts', 'person_id'))
				return "danger:The column person_id does not exist in table bank_accounts.";
			// Vérifie si la contrainte existe déjà (protection supplémentaire)
			$constraints = $connection->fetchAllAssociative(
				"SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
				WHERE TABLE_NAME = 'bank_accounts' AND COLUMN_NAME = 'person_id' AND REFERENCED_TABLE_NAME = 'persons'"
			);
			if (!empty($constraints))
				return "info:The relation between bank_accounts and persons already exists.";
			// Ajoute la contrainte de clé étrangère
			$sql = "ALTER TABLE bank_accounts 
					ADD CONSTRAINT fk_bank_person FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE";
			$connection->executeStatement($sql);
			return "success:Success! Relation created between bank_accounts and persons.";
		}
		catch (Throwable $e)
		{
			return "danger:Error while creating bank_accounts relation: " . $e->getMessage();
		}
	}

	public function createAddressRelation(Connection $connection): string
	{
		try
		{
			// Vérifie si la colonne person_id existe dans addresses
			if (!$this->doesColumnExist($connection, 'addresses', 'person_id'))
				return "danger:The column person_id does not exist in table addresses.";
			// Vérifie si la contrainte existe déjà
			$constraints = $connection->fetchAllAssociative(
				"SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
				WHERE TABLE_NAME = 'addresses' AND COLUMN_NAME = 'person_id' AND REFERENCED_TABLE_NAME = 'persons'"
			);
			if (!empty($constraints))
				return "info:The relation between addresses and persons already exists.";
			// Ajoute la contrainte de clé étrangère
			$sql = "ALTER TABLE addresses 
					ADD CONSTRAINT fk_address_person FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE";
			$connection->executeStatement($sql);
			return "success:Success! Relation created between addresses and persons.";
		}
		catch (Throwable $e)
		{
			return "danger:Error while creating addresses relation: " . $e->getMessage();
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
}
?>
