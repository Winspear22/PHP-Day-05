<?php

namespace App\Service;

use Exception;
use Throwable;
use RuntimeException;
use Doctrine\DBAL\Connection;

class DataReadService
{
	public function getAllData(Connection $connection, string $tableName): array
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
	public function getUserById(Connection $connection, string $tableName, int $id): ?array
	{
		try
		{
			$user = $connection->fetchAssociative("SELECT * FROM $tableName WHERE id = :id", ['id' => $id]);
			return $user ?: null;
		}
		catch (Throwable $e)
		{
			throw new RuntimeException('Error, we could not fetch the user, they probably do not exist : ' . $e->getMessage());
		}
	}

}
