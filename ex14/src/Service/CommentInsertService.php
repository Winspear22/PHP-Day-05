<?php

namespace App\Service;

use Throwable;
use Doctrine\DBAL\Connection;

class CommentInsertService
{
    public function insertCommentVulnerable(Connection $connection, string $tableName, string $comment): string
    {
        $sql = "INSERT INTO $tableName (comment)
                VALUES ('$comment')";
        try
        {
            $connection->executeStatement($sql);
            return "success:Success! Non-secure comment inserted!";
        }
        catch (Throwable $e)
        {
            return "danger:Error, there was a problem with the database or the table $tableName : " . $e->getMessage();
        }
    }
    public function insertCommentSafe(Connection $connection, string $tableName, string $comment): string
    {
        $sql = "INSERT INTO $tableName (comment)
                VALUES (:comment)";
        try
        {
            $connection->executeStatement($sql, ['comment' => $comment]);
            return "success:Success! Secure comment inserted!";
        }
        catch (Throwable $e)
        {
            return "danger:Error, there was a problem with the database or the table $tableName : " . $e->getMessage();
        }
    }
}
