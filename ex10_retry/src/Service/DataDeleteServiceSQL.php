<?php

namespace App\Service;

use Exception;
use Doctrine\DBAL\Connection;

class DataDeleteServiceSQL
{
    public function deleteData(Connection $connection, string $tableName, int $id): string
    {
        try
        {
            $exists = $connection->fetchOne("SELECT COUNT(*) FROM $tableName WHERE id = :id", ['id' => $id]);
            if (!$exists)
                return "danger:Error, data $id does not exist.";
            $connection->executeStatement("DELETE FROM $tableName WHERE id = :id", ['id' => $id]);
            return "success:Success! Data $id deleted.";
        }
        catch (Exception $e)
        {
            return "danger:Error while deleting data $id: " . $e->getMessage();
        }
    }
}
?>