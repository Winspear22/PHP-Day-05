<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class Ex04Controller extends AbstractController
{

    const SUCCESS = 0;
	const FAILURE = 1;
	const DOES_NOT_EXIST = 2;
    /**
     * @Route("/ex04", name="ex04_index")
     */
	public function index(Connection $connection): Response
	{
		$tableName = "users";
		try 
		{
			$tableStatus = $this->tableExistenceCheck($tableName, $connection);
			if ($tableStatus['status'] === self::DOES_NOT_EXIST) 
			{
				$this->addFlash('notice', $tableStatus['message']);
				$creationResult = $this->createTable($tableName, $connection);
				$this->addFlash('notice', $creationResult['message']);
				$genMessages = $this->generateUsers($connection, $tableName, 10);            
				foreach ($genMessages as $msg) 
					$this->addFlash('notice', $msg['message']);
			}
			$users = $this->getAllUsers($connection, $tableName);
		} 
		catch (\Exception $e) 
		{
			$this->addFlash('error', "Erreur globale : " . $e->getMessage());
			$users = [];
		}
			return $this->render('index.html.twig', [
			'users' => $users,
			]);
	}

	private function generateUsers(Connection $connection, string $tableName, int $nb): array
	{
		$messages = [];
		try 
		{
			$connection->executeStatement("DELETE FROM `$tableName`");
		} 
		catch (\Exception $e) 
		{
			$messages[] = ['status' => self::FAILURE, 'message' => "Erreur lors du nettoyage : " . $e->getMessage()];
			return $messages;
		}        
		for ($i = 0; $i <= $nb; $i++) 
		{
			$data = [
				'username'  => 'user'.$i,
				'name'      => 'Nom'.$i,
				'email'     => 'user'.$i.'@mail.com',
				'enable'    => rand(0,1),
				'birthdate' => date('Y-m-d H:i:s', strtotime('-'.rand(18,40).' years')),
				'address'   => 'Adresse '.$i.' avenue Testville'
			];
			try 
			{
				$sql = "INSERT INTO `$tableName` (username, name, email, enable, birthdate, address)
						VALUES (:username, :name, :email, :enable, :birthdate, :address)";
				$connection->executeStatement($sql, $data);
			} 
			catch (\Exception $e) 
			{
				$messages[] = ['status' => self::FAILURE, 'message' => "Erreur personne $i : " . $e->getMessage()];
			}
		}
		$messages[] = ['status' => self::SUCCESS, 'message' => "10 users créés avec succès."];
		return $messages;
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
            return ['status' => self::FAILURE, 'message' => "Erreur lors de la création de la table '$tableName' : " . $e->getMessage()];
        }
    }

    /**
     * @Route("/ex04/fill", name="ex04_fill", methods={"POST"})
     */
	public function fillTable(Connection $connection)
	{
		$tableName = "users";
		try 
		{
			$tableStatus = $this->tableExistenceCheck($tableName, $connection);
			if ($tableStatus['status'] === self::DOES_NOT_EXIST) 
			{
				$this->addFlash('notice', $tableStatus['message']);
				return $this->redirectToRoute('ex04_index');
			}
			$messages = $this->generateUsers($connection, $tableName, 10);
			foreach ($messages as $msg) 
				$this->addFlash('notice', $msg['message']);
		} 
		catch (\Exception $e) 
		{
			$this->addFlash('error', "Erreur lors du remplissage : " . $e->getMessage());
		}
		return $this->redirectToRoute('ex04_index');
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
	
	/*========================================================================================*/
	/*--------------------------------------- GETTER -----------------------------------------*/
	/*========================================================================================*/
	
	private function getAllUsers(Connection $connection, string $tableName): array
	{
		try 
		{
			$sql = "SELECT * FROM `$tableName` ORDER BY id ASC";
			return $connection->fetchAllAssociative($sql);
		} 
		catch (\Exception $e) 
		{
			return [];
		}
	}
    private function getPersonById(Connection $connection, string $tableName, int $id): ?array
    {
        try 
        {
            $sql = "SELECT * FROM `$tableName` WHERE id = :id";
            $person = $connection->fetchAssociative($sql, ['id' => $id]);
            return $person ?: null;
        } 
        catch (\Exception $e) 
        {
            return null;
        }
    }

	/*========================================================================================*/
	/*--------------------------------------- DELETE -----------------------------------------*/
	/*========================================================================================*/

    /**
     * @Route("/ex04/delete/{id}", name="ex04_delete", methods={"POST"})
     */
	public function delete(Connection $connection, Request $request, $id)
	{
		$tableName = "users";
		try 
		{
			$person = $this->getPersonById($connection, $tableName, (int)$id);
			if (!$person) 
			{
				$this->addFlash('notice', "Impossible de supprimer : le user (ID $id) n'existe pas.");
				return $this->redirectToRoute('ex04_index');
			}
			$sql = "DELETE FROM `$tableName` WHERE id = :id";
			$connection->executeStatement($sql, ['id' => $id]);
			$this->addFlash('success', "Suppression réussie pour le user ID $id !");
		} 
		catch (\Exception $e) 
		{
			$this->addFlash('error', "Erreur lors de la suppression ou du check : " . $e->getMessage());
		}
		$this->addFlash('notice', "Suppression effectuée avec succès.");
		return $this->redirectToRoute('ex04_index');
	}
}
