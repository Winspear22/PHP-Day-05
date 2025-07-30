<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Exception;

class UserDeleteService
{
    public function deleteUser(Connection $connection, string $tableName, int $id): string
    {
        try
        {
			$exists = $connection->fetchOne("SELECT COUNT(*) FROM $tableName WHERE id = :id", ['id' => $id]);
			if (!$exists)
				return "danger:User $id does not exist.";
            $connection->executeStatement("DELETE FROM $tableName WHERE id = :id", ['id' => $id]);
            return "success:Success! User $id deleted.";
        }
        catch (Exception $e)
        {
            return "danger:Error while deleting user $id: " . $e->getMessage();
        }
    }
}