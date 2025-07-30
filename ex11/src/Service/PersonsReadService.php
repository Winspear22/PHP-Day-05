<?php

namespace App\Service;

use Exception;
use Throwable;
use RuntimeException;
use Doctrine\DBAL\Connection;

class PersonsReadService
{
	public function getAllPersons(Connection $connection, string $tableName): array
	{
		$sql = "SELECT * FROM $tableName";
		try
		{
			return $connection->fetchAllAssociative($sql);
		}
		catch (Exception $e)
		{
			throw new RuntimeException("Error, we cannot display the person list, there was a problem in the table $tableName : " . $e->getMessage());
		}
	}
	public function getPersonById(Connection $connection, string $tableName, int $id): ?array
	{
		try
		{
			$person = $connection->fetchAssociative("SELECT * FROM $tableName WHERE id = :id", ['id' => $id]);
			return $person ?: null;
		}
		catch (Throwable $e)
		{
			throw new RuntimeException('Error, we could not fetch the person, they probably do not exist : ' . $e->getMessage());
		}
	}

	public function getPersonsWithDetailsAndFilter(
    Connection $connection,
    string $filterName = '',
    string $sortBy = 'name',
    string $sortDir = 'asc'
	): array
	{
		// Sécurité des valeurs
		$allowedSorts = ['name', 'email', 'birthdate'];
		$allowedDir = ['asc', 'desc'];
		if (!in_array($sortBy, $allowedSorts)) 
			$sortBy = 'name';
		if (!in_array($sortDir, $allowedDir))
			$sortDir = 'asc';

		$sql = "
			SELECT p.*, a.address, b.iban, b.bank_name
			FROM ex11_persons p
			LEFT JOIN ex11_addresses a ON a.person_id = p.id
			LEFT JOIN ex11_bank_accounts b ON b.person_id = p.id
			WHERE 1=1
		";
		$params = [];
		if ($filterName) {
			$sql .= " AND p.name LIKE :filterName ";
			$params['filterName'] = '%' . $filterName . '%';
		}
		$sql .= " ORDER BY p.$sortBy $sortDir, a.id ASC";

		return $connection->fetchAllAssociative($sql, $params);
	}

	public function getPersonsGrouped(Connection $connection, string $filterName = '', string $sortBy = 'name', string $sortDir = 'asc'): array
	{
		// Sécurité des valeurs
		$allowedSorts = ['name', 'email', 'birthdate'];
		$allowedDir = ['asc', 'desc'];
		if (!in_array($sortBy, $allowedSorts))  $sortBy = 'name';
		if (!in_array($sortDir, $allowedDir))   $sortDir = 'asc';

		$sql = "
			SELECT p.*, a.address, b.iban, b.bank_name
			FROM ex11_persons p
			LEFT JOIN ex11_addresses a ON a.person_id = p.id
			LEFT JOIN ex11_bank_accounts b ON b.person_id = p.id
			WHERE 1=1
		";
		$params = [];
		if ($filterName) {
			$sql .= " AND p.name LIKE :filterName ";
			$params['filterName'] = '%' . $filterName . '%';
		}
		$sql .= " ORDER BY p.$sortBy $sortDir, a.id ASC";

		$rows = $connection->fetchAllAssociative($sql, $params);

		// Groupement par personne (une ligne/personne, plusieurs adresses concaténées)
		$grouped = [];
		foreach ($rows as $row) {
			$pid = $row['id'];
			if (!isset($grouped[$pid])) {
				$grouped[$pid] = $row;
				$grouped[$pid]['addresses'] = [];
			}
			if ($row['address']) {
				$grouped[$pid]['addresses'][] = $row['address'];
			}
		}
		// Pour affichage, concatène les adresses avec <br>
		foreach ($grouped as &$person) {
			$person['addresses'] = implode('<br>', $person['addresses']);
		}
		unset($person);

		// Renvoie un tableau indexé (pas associatif)
		return array_values($grouped);
	}


}
