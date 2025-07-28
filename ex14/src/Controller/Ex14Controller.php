<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class Ex14Controller extends AbstractController
{
	const SUCCESS = 0;
	const FAILURE = 1;
	const DOES_NOT_EXIST = 2;
	/**
	 * @Route("/ex14", name="ex14_index")
	 */
	public function index(Connection $connection): Response
	{
		$tableName = "users";
		$tableStatus = $this->tableExistenceCheck($tableName, $connection);
		$rows = [];

		if ($tableStatus['status'] === self::SUCCESS) 
		{
			try 
			{
				$rows = $connection->fetchAllAssociative("SELECT * FROM $tableName");
			} 
			catch (\Exception $e) 
			{
				$this->addFlash('error', "Erreur lors de la récupération des données : " . $e->getMessage());
			}
		}

		return $this->render('index.html.twig', [
			'table_status' => $tableStatus,
			'rows' => $rows
		]);
	}

	/**
	 * @Route("/ex14/create-table", name="ex14_create_table")
	 */
	public function create_table(Connection $connection): Response
	{
		$tableName = "users";
		try 
		{
			$sql = "CREATE TABLE IF NOT EXISTS `$tableName` (
				id INT AUTO_INCREMENT PRIMARY KEY,
				username VARCHAR(255) UNIQUE
			) ENGINE=InnoDB;";
			$connection->executeStatement($sql);
			$this->addFlash('notice', "La table '$tableName' a été créée avec succès.");
		} 
		catch (\Exception $e) 
		{
			$this->addFlash('error', "Erreur lors de la CREATION de la table '$tableName', code erreur : " . $e->getMessage());
		}
		return $this->redirectToRoute('ex14_index');
	}


	private function tableExistenceCheck(string $tableName, Connection $connection): array
    {
		try
		{
			$doesTableExists = $connection->executeQuery("SHOW TABLES LIKE '$tableName'")->rowCount();
			if ($doesTableExists > 0)
				return (['status' => self::SUCCESS, 'message' => "La table $tableName existe déjà."]);
			return (['status' => self::DOES_NOT_EXIST]);
		}
		catch (\Exception $e)
		{
			return ['status' => self::FAILURE, 'message' => "Erreur lors de la RECHERCHE de la table '$tableName', code erreur : " . $e->getMessage()];
		}
	}

	/**
	 * @Route("/ex14/insert", name="ex14_insert", methods={"POST"})
	 */
	public function insert(Connection $connection, Request $request): Response
	{
		$tableName = "users";
		$username = $request->request->get('username');
		$sql = "INSERT INTO $tableName (username) VALUES ('$username')";
		try 
		{
			$connection->executeQuery($sql);
			$this->addFlash('notice', "Ajout OK !");
		} 
		catch (\Exception $e) 
		{
			$this->addFlash('error', "Erreur lors de l'ajout : " . $e->getMessage());
		}
		return $this->redirectToRoute('ex14_index');
	}

	/**
	 * @Route("/ex14/correct-insert", name="ex14_correct_insert", methods={"POST"})
	 */
	public function correctInsert(Connection $connection, Request $request): Response
	{
		$tableName = "users";
		$username = $request->request->get('username');
		try 
		{
			$sql = "INSERT INTO $tableName (username) VALUES (:username)";
			$connection->executeStatement($sql, ['username' => $username]);
			$this->addFlash('notice', "Ajout sécurisé OK !");
		} 
		catch (\Exception $e) 
		{
			$this->addFlash('error', "Erreur lors de l'ajout sécurisé : " . $e->getMessage());
		}
		return $this->redirectToRoute('ex14_index');
	}


}

?>