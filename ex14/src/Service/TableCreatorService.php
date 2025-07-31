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
            comment TEXT NOT NULL
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

    public function checkTableExistence(Connection $connection, string $tableName): bool
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
