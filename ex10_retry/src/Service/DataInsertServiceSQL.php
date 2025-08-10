<?php

namespace App\Service;

use Exception;
use DateTimeInterface;
use Doctrine\DBAL\Connection;

class DataInsertServiceSQL
{
    public function insertData(Connection $connection, string $tableName, string $data, DateTimeInterface $date): string
    {
        $sql = "INSERT INTO $tableName (data, date) VALUES (:data, :date)";
        try
        {
            $connection->executeStatement($sql, [
                'data' => $data,
                'date' => $date->format('Y-m-d H:i:s'),
            ]);
            return "success:Success! Data has been inserted into table $tableName.";
        }
        catch (Exception $e)
        {
            return "danger:Error, there was a problem inserting into table $tableName: " . $e->getMessage();
        }
    }
}
?>