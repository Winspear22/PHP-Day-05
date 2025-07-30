<?php

namespace App\Service;

use Exception;
use TypeError;
use DateTimeInterface;
use Doctrine\DBAL\Connection;

class UserUpdateService
{
    public function updateUser(Connection $connection, string $tableName, int $id, array $data): string
    {
        $sql = "UPDATE $tableName
                SET username = :username,
                    name = :name,
                    email = :email,
                    enable = :enable,
                    birthdate = :birthdate,
                    address = :address
                WHERE id = :id";
        try
        {
			$user = $connection->fetchOne("SELECT COUNT(*) FROM $tableName WHERE id = :id", ['id' => $id]);
            if (!$user)
                return "danger:Error - User $id does not exist in the table $tableName.";
            $connection->executeStatement($sql, [
                'username'  => $data['username'],
                'name'      => $data['name'],
                'email'     => $data['email'],
                'enable'    => $data['enable'] ? 1 : 0,
                'birthdate' => $data['birthdate'] instanceof DateTimeInterface
                    ? $data['birthdate']->format('Y-m-d H:i:s')
                    : $data['birthdate'],
                'address'   => $data['address'],
                'id'        => $id,
            ]);
            return "success:Success! User $id has been updated.";
        }
        catch (Exception $e)
        {
            $msg = $e->getMessage();
            if (stripos($msg, 'Duplicate entry') !== false || stripos($msg, 'UNIQUE constraint failed') !== false)
                return "danger:Error, duplicate entry! The username or the email already exists.";
            if (stripos($msg, 'constraint') !== false || stripos($msg, 'violat') !== false)
                return "danger:Error, database constraint violation: $msg";
            if ($e instanceof TypeError)
                return "danger:Type error: $msg";
            return "danger:Error, we could not update the user, unexpected error: $msg";
        }
    }
}
?>