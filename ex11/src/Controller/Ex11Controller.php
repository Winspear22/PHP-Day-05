<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Ex11Controller extends AbstractController
{
    const SUCCESS = 0;
	const FAILURE = 1;
	const DOES_NOT_EXIST = 2;

	/**
	 * @Route("/ex11", name="ex11_index")
	 */
	public function index(Connection $connection, Request $request): Response
	{
		try
		{
			$requiredTables = [
				'persons' => function() use ($connection) { $this->createPersonsHelper('persons', $connection); },
				'addresses' => function() use ($connection) { $this->createAddressesHelper('addresses', $connection); },
				'bank_accounts' => function() use ($connection) { $this->createBankAccountsHelper('bank_accounts', $connection); },
			];
			$messages = [];
			foreach ($requiredTables as $table => $creator)
			{
				$exists = $this->tableExistenceCheck($table, $connection);
				if ($exists['status'] === self::DOES_NOT_EXIST)
				{
					$result = $creator();
					$messages[] = "Table $table créée automatiquement.";
				}
			}
			$relations = [
				[
					'table' => 'addresses',
					'column' => 'person_id',
					'setup' => function() use ($connection) { $this->addRelationAddresses($connection); }
				],
				[
					'table' => 'bank_accounts',
					'column' => 'person_id',
					'setup' => function() use ($connection) { $this->addRelationBankAccount($connection); }
				],
			];

			foreach ($relations as $rel)
			{
				$hasCol = $this->columnExistenceCheck($connection, $rel['table'], $rel['column']);
				if ($hasCol['status'] === self::DOES_NOT_EXIST)
				{
					$rel['setup']();
					$messages[] = "Relation sur {$rel['table']}.{$rel['column']} créée automatiquement.";
				}
			}

			// GET param, filtres
			$filterName = $request->query->get('filter_name', '');
			$sortBy = $request->query->get('sort_by', 'name');
			$sortDir = $request->query->get('sort_dir', 'asc');

			// Protections
			$allowedSorts = ['name', 'email', 'city', 'birthdate'];
			$allowedDir = ['asc', 'desc'];
			if (!in_array($sortBy, $allowedSorts))
			{
				$this->addFlash('notice', '⚠️ Tri non valide, utilisation du tri par défaut.');
				$sortBy = 'name';
			}
			if (!in_array($sortDir, $allowedDir))
			{
				$this->addFlash('notice', '⚠️ Sens de tri non valide, utilisation du tri par défaut.');
				$sortDir = 'asc';
			}
			if (mb_strlen($filterName) > 80)
			{
				$this->addFlash('notice', '⚠️ Filtre trop long ! Limité à 80 caractères.');
				$filterName = mb_substr($filterName, 0, 80);
			}
			if (!preg_match('/^[\p{L}\p{N} _\'\-]*$/u', $filterName))
			{
				$this->addFlash('notice', '⚠️ Le filtre contient des caractères non autorisés.');
				$filterName = preg_replace('/[^\p{L}\p{N} _\'\-]/u', '', $filterName);
			}

			$sql = "
				SELECT p.id, p.name, p.email, p.birthdate, a.city, a.street, b.iban, b.bank_name
				FROM persons p
				LEFT JOIN addresses a ON a.person_id = p.id
				LEFT JOIN bank_accounts b ON b.person_id = p.id
				WHERE 1=1
			";
			$params = [];
			if ($filterName)
			{
				$sql .= " AND p.name LIKE :filterName ";
				$params['filterName'] = '%' . $filterName . '%';
			}
			$sql .= " ORDER BY $sortBy $sortDir";

			$data = $connection->fetchAllAssociative($sql, $params);
			$total_people = $connection->fetchOne('SELECT COUNT(*) FROM persons');

			return $this->render('index.html.twig', [
				'data' => $data,
				'filter_name' => $filterName,
				'sort_by' => $sortBy,
				'sort_dir' => $sortDir,
				'total_people' => $total_people,
			]);
		}
		catch (\Exception $e)
		{
			$this->addFlash('notice', "Erreur d'accès à la base de données : " . $e->getMessage());
			return $this->render('index.html.twig', [
				'data' => [],
				'filter_name' => '',
				'sort_by' => 'name',
				'sort_dir' => 'asc',
				'total_people' => 0,
			]);
		}
	}

    /*========================================================================================*/
	/*---------------------------------- CREATE TABLES ---------------------------------------*/
	/*========================================================================================*/

    private function createPersonsHelper(string $tableName, Connection $connection): array
    {
        try 
        {
			$exists = $this->tableExistenceCheck($tableName, $connection);
			if ($exists['status'] === self::FAILURE)
				return ['status' => self::FAILURE, 'message' => $exists['message']];
			if ($exists['status'] === self::DOES_NOT_EXIST)
			{
				$sql = "CREATE TABLE IF NOT EXISTS `$tableName` (
					id INT AUTO_INCREMENT PRIMARY KEY,
					username VARCHAR(255) UNIQUE,
					name VARCHAR(255),
					email VARCHAR(255) UNIQUE,
					enable BOOLEAN,
					birthdate DATETIME
					) ENGINE=InnoDB;";
					$connection->executeStatement($sql);
					return ['status' => self::SUCCESS, 'message' => "La table '$tableName' a été créée avec succès."];
			}
			return ['status' => self::SUCCESS, 'message' => "La table '$tableName' existe déjà."];
		}
        catch (\Exception $e) 
        {
            return ['status' => self::FAILURE, 'message' => "Erreur lors de la création de la table '$tableName' : " . $e->getMessage()];
        }
    }
	
	private function createAddressesHelper(string $tableName, Connection $connection): array
	{
		try
		{
			$exists = $this->tableExistenceCheck($tableName, $connection);
			if ($exists['status'] === self::FAILURE)
				return ['status' => self::FAILURE, 'message' => $exists['message']];
			if ($exists['status'] === self::DOES_NOT_EXIST)
			{
				$sql = "CREATE TABLE `$tableName` (
					id INT AUTO_INCREMENT PRIMARY KEY,
					street VARCHAR(255) NOT NULL,
					city VARCHAR(255) NOT NULL,
					country VARCHAR(255) NOT NULL
				)";
				$connection->executeStatement($sql);
				return ['status' => self::SUCCESS, 'message' => "La table '$tableName' a été créée avec succès."];
			}
			return ['status' => self::SUCCESS, 'message' => "La table '$tableName' existe déjà."];
		}
		catch (\Exception $e)
		{
			return ['status' => self::FAILURE, 'message' => "Erreur lors de la création de la table '$tableName' : " . $e->getMessage()];
		}
	}

	private function createBankAccountsHelper(string $tableName, Connection $connection): array
	{
		try
		{
			$exists = $this->tableExistenceCheck($tableName, $connection);
			if ($exists['status'] === self::FAILURE)
				return ['status' => self::FAILURE, 'message' => $exists['message']];
			if ($exists['status'] === self::DOES_NOT_EXIST)
			{
				$sql = "CREATE TABLE `$tableName` (
					id INT AUTO_INCREMENT PRIMARY KEY,
					iban VARCHAR(34) NOT NULL UNIQUE,
					bank_name VARCHAR(255) NOT NULL
					)";
				$connection->executeStatement($sql);
				return ['status' => self::SUCCESS, 'message' => "La table '$tableName' a été créée avec succès."];
			}
			return ['status' => self::SUCCESS, 'message' => "La table '$tableName' existe déjà."];
		}
		catch (\Exception $e)
		{
			return ['status' => self::FAILURE, 'message' => "Erreur lors de la création de la table '$tableName' : " . $e->getMessage()];
		}
	}



	private function tableExistenceCheck(string $tableName, Connection $connection): array
    {
        try 
        {
            $doesTableExists = $connection->executeQuery("SHOW TABLES LIKE '$tableName'")->rowCount();
            if ($doesTableExists > 0)
                return ['status' => self::SUCCESS, 'message' => "La table $tableName existe déjà."];
            return ['status' => self::DOES_NOT_EXIST, 'message' => "La table $tableName n'existe pas... "];
        }
        catch (\Exception $e) 
        {
            return ['status' => self::FAILURE, 'message' => "Erreur lors de la RECHERCHE de la table '$tableName' : " . $e->getMessage()];
        }
    }

	private function columnExistenceCheck(Connection $connection, string $tableName, string $columnName): array
    {
        try 
        {
            $sql = "SHOW COLUMNS FROM `$tableName` LIKE :columnName";
            $result = $connection->executeQuery($sql, ['columnName' => $columnName]);
            if ($result->rowCount() > 0)
                return ['status' => self::SUCCESS, 'message' => "La colonne '$columnName' existe déjà dans '$tableName'."];
            return ['status' => self::DOES_NOT_EXIST, 'message' => "La colonne '$columnName' n'existe pas dans '$tableName'."];
        } 
        catch (\Exception $e) 
        {
            return ['status' => self::FAILURE, 'message' => "Erreur lors de la vérification de la colonne '$columnName' dans '$tableName' : " . $e->getMessage()];
        }
    }

    /*========================================================================================*/
	/*------------------------------------ ADD RELATIONS -------------------------------------*/
	/*========================================================================================*/

	private function addRelationAddresses(Connection $connection)
	{
		$columnStatus = $this->columnExistenceCheck($connection, 'addresses', 'person_id');
		if ($columnStatus['status'] === self::FAILURE) 
		{
			$this->addFlash('notice', $columnStatus['message']);
			return $this->redirectToRoute('ex11_index');
		}
		if ($columnStatus['status'] === self::DOES_NOT_EXIST) 
		{
			try 
			{
				$connection->executeStatement("
					ALTER TABLE addresses
					ADD COLUMN person_id INT,
					ADD CONSTRAINT fk_person_addr
					FOREIGN KEY (person_id) REFERENCES persons(id)
					ON DELETE SET NULL
				");
				$message = "Relation one-to-many persons/addresses créée !";
			} 
			catch (\Exception $e) 
			{
				$message = "Erreur dans la création de la liaison persons/addresses : " . $e->getMessage();
			}
		} 
		else if ($columnStatus['status'] === self::SUCCESS)
			$message = "La relation persons/addresses existe déjà.";

		$this->addFlash('notice', $message);
		return $this->redirectToRoute('ex11_index');
	}

	private function addRelationBankAccount(Connection $connection)
	{
		$columnStatus = $this->columnExistenceCheck($connection, 'bank_accounts', 'person_id');
		if ($columnStatus['status'] === self::FAILURE) 
		{
			$this->addFlash('notice', $columnStatus['message']);
			return $this->redirectToRoute('ex11_index');
		}
		if ($columnStatus['status'] === self::DOES_NOT_EXIST) 
		{
			try 
			{
				$connection->executeStatement("
					ALTER TABLE bank_accounts
					ADD COLUMN person_id INT UNIQUE,
					ADD CONSTRAINT fk_person_bank
					FOREIGN KEY (person_id) REFERENCES persons(id)
					ON DELETE SET NULL
				");
				$message = "Relation one-to-one persons/bank_accounts créée !";
			} 
			catch (\Exception $e) 
			{
				$message = "Erreur dans la création de la liaison persons/bank_accounts : " . $e->getMessage();
			}
		} 
		else if ($columnStatus['status'] === self::SUCCESS)
			$message = "La relation persons/bank_accounts existe déjà.";

		$this->addFlash('notice', $message);
		return $this->redirectToRoute('ex11_index');
	}

    /*========================================================================================*/
	/*------------------------------------ DROP TABLES ---------------------------------------*/
	/*========================================================================================*/

	/**
	 * @Route("/ex11/drop-tables", name="ex11_drop_tables")
	 */
	public function dropTables(Connection $connection)
	{
		$message = "";
		try 
		{
			$bankSql = "DROP TABLE IF EXISTS bank_accounts";
			$addrSql = "DROP TABLE IF EXISTS addresses";
			$persSql = "DROP TABLE IF EXISTS persons";
			$connection->executeStatement($bankSql);
			$connection->executeStatement($addrSql);
			$connection->executeStatement($persSql);
			$message = "Les tables bank_accounts, addresses et persons ont bien été supprimées (si elles existaient) !";
		} 
		catch (\Exception $e)
		{
			$message = "Erreur lors de la suppression des tables : " . $e->getMessage();
		}
		$this->addFlash('notice', $message);
		return $this->redirectToRoute('ex11_index');
	}
    
    /*========================================================================================*/
	/*--------------------------------- ADD TESTS PERSONS ------------------------------------*/
	/*========================================================================================*/

	/**
	 * @Route("/ex11/add-test-persons", name="ex11_add_test_persons")
	 */
	public function addTestPersons(Connection $connection)
	{
		$messages = [];
		$message = "";

		for ($i = 1; $i <= 10; $i++) 
		{
			$username = "user$i";
			$name = $this->randomAlphaString(5) . "Nom$i"; // Modifier ici pour démontrer que Nom1 et Nom10 sortent en même temps
			$email = "user{$i}@mail.com";
			$enable = rand(0,1);
			$birthdate = date('Y-m-d H:i:s', strtotime('-'.rand(18,40).' years'));

			$person = $this->addTestPerson($connection, $username, $name, $email, $enable, $birthdate);

			if ($person['status'] === self::SUCCESS) 
			{
				$personId = $person['id'];
				$address = $this->addTestAddress(
					$connection,
					"Adresse {$i} avenue Testville",
					"Ville".chr(65+$i),
					"France",
					$personId
				);
				$bank = $this->addTestBankAccount(
					$connection,
					'FR76' . str_pad($i, 20, '0', STR_PAD_LEFT),
					"Bank{$i}",
					$personId
				);
				//$messages[] = $person['message'];
				//$messages[] = $address['message'];
				//$messages[] = $bank['message'];
				$message = "Création des données réussies.";
			} 
			else 
			{
				$message = "Erreur lors de la création des données.";
				$messages[] = $person['message'];
			}
		}
		//$this->addFlash('notice', implode('<br>', $messages));
		$this->addFlash('notice',$message);
		return $this->redirectToRoute('ex11_index');
	}

	private function addTestPerson(Connection $connection, string $username, string $name, string $email, int $enable, string $birthdate): array
	{
		try {
			$sql = "INSERT INTO persons (username, name, email, enable, birthdate) VALUES (?, ?, ?, ?, ?)";
			$connection->executeStatement($sql, [$username, $name, $email, $enable, $birthdate]);
			$id = $connection->lastInsertId();
			return [
				'status' => self::SUCCESS,
				'message' => "Personne '$username' ajoutée avec succès.",
				'id' => $id
			];
		} catch (\Exception $e) {
			return [
				'status' => self::FAILURE,
				'message' => "Erreur lors de l'ajout de la personne '$username' : " . $e->getMessage(),
				'id' => null
			];
		}
	}

	private function randomAlphaString($length = 8) 
	{
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$result = '';
		for ($i = 0; $i < $length; $i++) 
		{
			$result .= $characters[random_int(0, strlen($characters) - 1)];
		}	
		return $result;
	}

	private function addTestAddress(Connection $connection, string $street, string $city, string $country, int $personId): array
	{
		try {
			$sql = "INSERT INTO addresses (street, city, country, person_id) VALUES (?, ?, ?, ?)";
			$connection->executeStatement($sql, [$street, $city, $country, $personId]);
			return [
				'status' => self::SUCCESS,
				'message' => "Adresse '$street' ajoutée à la personne ID $personId."
			];
		} catch (\Exception $e) {
			return [
				'status' => self::FAILURE,
				'message' => "Erreur lors de l'ajout de l'adresse '$street' : " . $e->getMessage()
			];
		}
	}

	private function addTestBankAccount(Connection $connection, string $iban, string $bankName, int $personId): array
	{
		try {
			$sql = "INSERT INTO bank_accounts (iban, bank_name, person_id) VALUES (?, ?, ?)";
			$connection->executeStatement($sql, [$iban, $bankName, $personId]);
			return [
				'status' => self::SUCCESS,
				'message' => "Compte bancaire '$iban' ajouté à la personne ID $personId."
			];
		} catch (\Exception $e) {
			return [
				'status' => self::FAILURE,
				'message' => "Erreur lors de l'ajout du compte bancaire '$iban' : " . $e->getMessage()
			];
		}
	}
}
