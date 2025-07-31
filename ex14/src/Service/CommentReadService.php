<?php

namespace App\Service;

use Exception;
use Throwable;
use RuntimeException;
use Doctrine\DBAL\Connection;

class CommentReadService
{
	public function getAllComments(Connection $connection, string $tableName): array
	{
		$sql = "SELECT * FROM $tableName";
		try
		{
			return $connection->fetchAllAssociative($sql);
		}
		catch (Exception $e)
		{
			throw new RuntimeException("Error, we cannot display the comments list, there was a problem in the table $tableName : " . $e->getMessage());
		}
	}
	public function getCommentById(Connection $connection, string $tableName, int $id): ?array
	{
		try
		{
			$comment = $connection->fetchAssociative("SELECT * FROM $tableName WHERE id = :id", ['id' => $id]);
			return $comment ?: null;
		}
		catch (Throwable $e)
		{
			throw new RuntimeException('Error, we could not fetch the comment, it probably does not exist : ' . $e->getMessage());
		}
	}

	public function validateComment(string $comment, int $maxLength = 64): ?string
	{
		$comment = trim($comment);
		if ($comment === '')
			return 'Error, comment cannot be blank.';
		if (mb_strlen($comment) > $maxLength)
			return "Error, comment is too long (max $maxLength characters).";
		return null;
	}


}
