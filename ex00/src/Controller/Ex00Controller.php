<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Ex00Controller extends AbstractController
{
	const SUCCESS = 0;
	const FAILURE = 1;
	const DOES_NOT_EXIST = 2;

	/**
	 * @Route("/ex00", name="ex00_index")
	 */
	public function index(Connection $connection, Request $request): Response
	{
		$tableName = "user";

		if ($request->isMethod('POST')) 
		{
			$doesTableExists = $this->tableExistenceCheck($tableName, $connection);
			if ($doesTableExists['status'] === self::DOES_NOT_EXIST) 
			{
				$createTable = $this->createTable($tableName, $connection);
				$this->addFlash('notice', $createTable['message']);
			}
			else
				$this->addFlash('notice', $doesTableExists['message']);
			return $this->redirectToRoute('ex00_index');
		}
		return $this->render('index.html.twig', [
			'tableName' => $tableName
		]);
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

	private function createTable(string $tableName, Connection $connection): array
	{
		try 
		{
			$sql = "CREATE TABLE IF NOT EXISTS `$tableName` (
				id INT AUTO_INCREMENT PRIMARY KEY,
				username VARCHAR(255) UNIQUE,
				name VARCHAR(255),
				email VARCHAR(255) UNIQUE,
				enable BOOLEAN,
				birthdate DATETIME,
				address LONGTEXT
			) ENGINE=InnoDB;";
			$connection->executeStatement($sql);
			return ['status' => self::SUCCESS, 'message' => "La table '$tableName' a été créée avec succès."];
		} 
		catch (\Exception $e) 
		{
			return ['status' => self::FAILURE, 'message' => "Erreur lors de la CREATION de la table '$tableName', code erreur : " . $e->getMessage()];
		}
	}
}
