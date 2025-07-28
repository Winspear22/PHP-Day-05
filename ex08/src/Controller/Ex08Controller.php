<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Ex08Controller extends AbstractController
{

    const SUCCESS = 0;
	const FAILURE = 1;
	const DOES_NOT_EXIST = 2;
    /**
     * @Route("/ex08", name="ex08_index")
     */
	public function index(Connection $connection): Response
	{
		try
		{
			// État des tables
			$tables = ['persons', 'addresses', 'bank_accounts'];
			$tablesStatus = [];
			foreach ($tables as $table)
				$tablesStatus[$table] = $this->tableExistenceCheck($table, $connection)['status'];

			// Colonnes et relations
			$maritalStatus = ($tablesStatus['persons'] === self::SUCCESS)
				? $this->columnExistenceCheck($connection, 'persons', 'marital_status')['status']
				: self::DOES_NOT_EXIST;

			$relationAddresses = ($tablesStatus['addresses'] === self::SUCCESS)
				? $this->columnExistenceCheck($connection, 'addresses', 'person_id')['status']
				: self::DOES_NOT_EXIST;

			$relationBankAccount = ($tablesStatus['bank_accounts'] === self::SUCCESS)
				? $this->columnExistenceCheck($connection, 'bank_accounts', 'person_id')['status']
				: self::DOES_NOT_EXIST;
		}
		catch (\Exception $e)
		{
			// Tout a planté, tu renvoies le minimum pour que la page ne crash pas
			$this->addFlash('notice', "Erreur lors de l'accès à la base de données : " . $e->getMessage());
			$tablesStatus = ['persons'=> self::DOES_NOT_EXIST, 'addresses'=> self::DOES_NOT_EXIST, 'bank_accounts'=> self::DOES_NOT_EXIST];
			$maritalStatus = self::DOES_NOT_EXIST;
			$relationAddresses = self::DOES_NOT_EXIST;
			$relationBankAccount = self::DOES_NOT_EXIST;
		}

		return $this->render('index.html.twig', [
			'tablesStatus'        => $tablesStatus,
			'maritalStatus'       => $maritalStatus,
			'relationAddresses'   => $relationAddresses,
			'relationBankAccount' => $relationBankAccount,
		]);
	}

    
	/*========================================================================================*/
	/*--------------------------------------- CREATE TABLES -----------------------------------------*/
	/*========================================================================================*/

		/**
	 * @Route("/ex08/create-persons", name="ex08_create_persons")
	 */
	public function createPersonsTable(Connection $connection)
	{
		$tableName = 'persons';
		$result = $this->createPersonsHelper($tableName, $connection);
		$this->addFlash('notice', $result['message']);
		return $this->redirectToRoute('ex08_index');
	}

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
	
	/**
	 * @Route("/ex08/create-address-table", name="ex08_create_address_tables")
	 */
	public function createAddressesTable(Connection $connection)
	{
		$tableName = 'addresses';
		$result = $this->createAddressesHelper($tableName, $connection);
		$this->addFlash('notice', $result['message']);
		return $this->redirectToRoute('ex08_index');
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

	/**
     * @Route("/ex08/create-BankAccount-table", name="ex08_create_BankAccount_tables")
     */
	public function createBankAccountsTable(Connection $connection)
	{
		$tableName = 'bank_accounts';
		$result = $this->createBankAccountsHelper($tableName, $connection);
		$this->addFlash('notice', $result['message']);
		return $this->redirectToRoute('ex08_index');
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
	/*--------------------------------- ADD MARITAL STATUS -----------------------------------*/
	/*========================================================================================*/

	/**
	 * @Route("/ex08/add-relation-addresses", name="ex08_add_relation_addresses")
	 */
	public function addRelationAddresses(Connection $connection)
	{
		$columnStatus = $this->columnExistenceCheck($connection, 'addresses', 'person_id');
		if ($columnStatus['status'] === self::FAILURE) 
		{
			$this->addFlash('notice', $columnStatus['message']);
			return $this->redirectToRoute('ex08_index');
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
		return $this->redirectToRoute('ex08_index');
	}

	/**
	 * @Route("/ex08/add-relation-bank-account", name="ex08_add_relation_bank_account")
	 */
	public function addRelationBankAccount(Connection $connection)
	{
		$columnStatus = $this->columnExistenceCheck($connection, 'bank_accounts', 'person_id');
		if ($columnStatus['status'] === self::FAILURE) 
		{
			$this->addFlash('notice', $columnStatus['message']);
			return $this->redirectToRoute('ex08_index');
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
		return $this->redirectToRoute('ex08_index');
	}

	/**
	 * @Route("/ex08/add-marital-status", name="ex08_add_marital_status")
	 */
	public function addMaritalStatusColumn(Connection $connection)
	{
		$columnStatus = $this->columnExistenceCheck($connection, 'persons', 'marital_status');
		if ($columnStatus['status'] === self::FAILURE) 
		{
			$this->addFlash('notice', $columnStatus['message']);
			return $this->redirectToRoute('ex08_index');
		}
		if ($columnStatus['status'] === self::DOES_NOT_EXIST) 
		{
			try 
			{
				$connection->executeStatement("
					ALTER TABLE persons
					ADD COLUMN marital_status ENUM('single','married','widower') NOT NULL DEFAULT 'single'
				");
				$message = "Colonne 'marital_status' ajoutée avec succès à 'persons'.";
			} 
			catch (\Exception $e) {
				$message = "Erreur lors de l'ajout de la colonne marital_status : " . $e->getMessage();
			}
		}
		else if ($columnStatus['status'] === self::SUCCESS)
			$message = "La colonne 'marital_status' existe déjà dans 'persons'.";

		$this->addFlash('notice', $message);
		return $this->redirectToRoute('ex08_index');
	}

	/*========================================================================================*/
	/*------------------------------------ DROP TABLES ---------------------------------------*/
	/*========================================================================================*/

	/**
	 * @Route("/ex08/drop-tables", name="ex08_drop_tables")
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
		return $this->redirectToRoute('ex08_index');
	}
}
