<?php

namespace App\Service;

use Exception;
use RuntimeException;
use Doctrine\DBAL\Connection;

class DataReadServiceSQL
{
    public function getAllRecords(Connection $connection, string $tableName): array
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

    public function getRecordById(Connection $connection, string $tableName, int $id): ?array
    {
        try
        {
            $record = $connection->fetchAssociative("SELECT * FROM $tableName WHERE id = :id", ['id' => $id]);
            return $record ?: null;
        }
        catch (Exception $e)
        {
            throw new RuntimeException("Error: Could not fetch the record with id $id: " . $e->getMessage());
        }
    }
}
