<?php

namespace App\Service;

use Exception;
use Doctrine\DBAL\Connection;

class TableCreatorService
{
    public function createTable(Connection $connection, string $tableName): string
    {
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(25) UNIQUE NOT NULL,
            name VARCHAR(25) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            enable BOOLEAN NOT NULL DEFAULT 1,
            birthdate DATETIME NOT NULL,
            address LONGTEXT NOT NULL
        )";
        try
        {
            if ($this->checkTableExistence($connection, $tableName))
                return "info:The table $tableName already exists and cannot be created again.";
            $connection->executeStatement($sql);
            return "success:Success! The table $tableName was created!";
        }
        catch (Exception $e)
        {
            return "danger:Error, there was a problem in the table $tableName creation : " . $e->getMessage();
        }
    }

    private function checkTableExistence(Connection $connection, string $tableName): bool
    {
        try
        {
            $result = $connection->fetchOne("SHOW TABLES LIKE '$tableName'");
            return ($result !== false);
        }
        catch (Exception $e)
        {
            return false;
        }
    }
}
