<?php

namespace App\Service;

use Exception;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class UserInsertService
{
	public function insertUser(Connection $connection, string $tableName, array $data): string
    {
		$sql = "INSERT INTO $tableName (username, name, email, enable, birthdate, address)
                VALUES (:username, :name, :email, :enable, :birthdate, :address)";
        try
        {
            $connection->executeStatement($sql, [
                'username'  => $data['username'],
                'name'      => $data['name'],
                'email'     => $data['email'],
                'enable'    => $data['enable'] ? 1 : 0,
                'birthdate' => $data['birthdate'],
                'address'   => $data['address'],
            ]);
            return "success:OK";
        }
        catch (UniqueConstraintViolationException $e)
        {
            return "danger:Error - There is a duplicate entry ! Username or email already exists.";
        }
        catch (Exception $e)
        {
            return "danger:Error, there was a problem with the database or the table $tableName : " . $e->getMessage();
        }
    }
}
