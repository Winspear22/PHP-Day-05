<?php

namespace App\Service;

use Exception;
use RuntimeException;
use Doctrine\DBAL\Connection;

class DataReadServiceSQL
{
    public function getAllDatas(Connection $connection, string $tableName): array
    {
        $sql = "SELECT * FROM $tableName ORDER BY id ASC";
        try
        {
            return $connection->fetchAllAssociative($sql);
        }
        catch (Exception $e)
        {
            throw new RuntimeException("Error: Cannot display the data list for table $tableName: " . $e->getMessage());
        }
    }

    public function getDataById(Connection $connection, string $tableName, int $id): ?array
    {
        try
        {
            $data = $connection->fetchAssociative("SELECT * FROM $tableName WHERE id = :id", ['id' => $id]);
            return $data ?: null;
        }
        catch (Exception $e)
        {
            throw new RuntimeException("Error: Could not fetch the data with id $id: " . $e->getMessage());
        }
    }
}
?>