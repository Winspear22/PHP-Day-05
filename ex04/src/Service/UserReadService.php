<?php

namespace App\Service;

use Exception;
use RuntimeException;
use Doctrine\DBAL\Connection;

class UserReadService
{
	public function getAllUsers(Connection $connection, string $tableName): array
	{
		$sql = "SELECT * FROM $tableName";
		try
		{
			return $connection->fetchAllAssociative($sql);
		}
		catch (Exception $e)
		{
			throw new RuntimeException("Error, we cannot display the user list, there was a problem in the table $tableName : " . $e->getMessage());
		}
	}
}
