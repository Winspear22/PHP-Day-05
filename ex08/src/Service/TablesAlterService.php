<?php

namespace App\Service;

use Exception;
use Doctrine\DBAL\Connection;

class TablesAlterService
{
    public function addMaritalStatusToPersons(Connection $connection, string $tableName): string
    {
        $sql = "ALTER TABLE $tableName ADD COLUMN marital_status ENUM('single', 'married', 'widower') DEFAULT 'single'";
        try
        {
            // Vérifie si la colonne existe déjà pour éviter les erreurs
			if ($this->doesColumnExist($connection, 'persons', 'marital_status'))
				return "info:The column marital_status already exists in table persons.";
            $connection->executeStatement($sql);
            return "success:Success! The column marital_status was added to table $tableName.";
        }
        catch (Exception $e)
        {
            return "danger:Error while altering table $tableName: " . $e->getMessage();
        }
    }

	public function doesColumnExist(Connection $connection, string $tableName, string $columnName): bool
	{
		try
		{
			$columns = $connection->fetchAllAssociative("SHOW COLUMNS FROM $tableName LIKE :column", ['column' => $columnName]);
			return count($columns) > 0;
		}
		catch (Exception $e)
		{
			return false;
		}
	}
}
?>
