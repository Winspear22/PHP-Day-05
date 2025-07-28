<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Ex06Controller extends AbstractController
{
    const SUCCESS = 0;
	const FAILURE = 1;
	const DOES_NOT_EXIST = 2;


	/**
	* @Route("/ex06", name="ex06_index")
	*/
	public function index(Connection $connection, Request $request): Response
	{
		try
		{
			$tableName = "persons";

			// 1. Vérifier et créer table si besoin, puis recharger la liste
			$tableStatus = $this->tableExistenceCheck($tableName, $connection);
			if ($tableStatus['status'] === self::DOES_NOT_EXIST) 
			{
				$this->addFlash('notice', $tableStatus['message']);
				$creationResult = $this->createTable($tableName, $connection);
				$this->addFlash('notice', $creationResult['message']);
				$genMessages = $this->generatePersons($connection, $tableName, 10);
				foreach ($genMessages as $msg) 
				{
					$this->addFlash('notice', $msg['message']);
				}
			}
			$persons = $this->getAllPersons($connection, $tableName);

			// 2. Gérer le paramètre d’édition
			$editId = $request->query->get('edit');
			$editPerson = null;
			if ($editId) 
			{
				$editPerson = $this->getPersonById($connection, $tableName, (int)$editId);
				if (!$editPerson)
					$this->addFlash('notice', "Personne introuvable (ID $editId)");
			}

			// 3. Traitement du formulaire d'édition (POST)
			if ($request->isMethod('POST') && $editId) {
				$data = [
					'username'  => $request->request->get('username'),
					'name'      => $request->request->get('name'),
					'email'     => $request->request->get('email'),
					'enable'    => $request->request->get('enable', 0),
					'birthdate' => $request->request->get('birthdate'),
					'address'   => $request->request->get('address'),
				];
				$updateResult = $this->updatePerson($connection, $tableName, (int)$editId, $data);
				$this->addFlash('notice', $updateResult['message']);
				return $this->redirectToRoute('ex06_index');
			}

			// 4. Affichage
			return $this->render('index.html.twig', [
				'persons'    => $persons,
				'editPerson' => $editPerson
			]);
		}
		catch (\Exception $e)
		{
			$this->addFlash('error', 'Erreur générale : ' . $e->getMessage());
			return $this->render('index.html.twig', [
				'persons'    => [],
				'editPerson' => null,
			]);
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


    private function generatePersons(Connection $connection, string $tableName, int $nb): array
    {
        $messages = [];
        for ($i = 1; $i <= $nb; $i++) 
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
                $messages[] = ['status' => self::SUCCESS, 'message' => "Personne $i ajoutée avec succès."];
            } 
			catch (\Exception $e) 
			{
                $messages[] = ['status' => self::FAILURE, 'message' => "Erreur personne $i : " . $e->getMessage()];
            }
        }
        return $messages;
    }


    private function updatePerson(Connection $connection, string $tableName, int $id, array $data): array
    {
        try 
        {
            $sql = "UPDATE `$tableName` 
                    SET username = :username,
                        name = :name,
                        email = :email,
                        enable = :enable,
                        birthdate = :birthdate,
                        address = :address
                    WHERE id = :id";
            $connection->executeStatement($sql, [
                'username'  => $data['username'],
                'name'      => $data['name'],
                'email'     => $data['email'],
                'enable'    => !empty($data['enable']) ? 1 : 0,
                'birthdate' => $data['birthdate'],
                'address'   => $data['address'],
                'id'        => $id
            ]);
            return ['status' => self::SUCCESS, 'message' => "Modification réussie !"];
        } 
        catch (\Exception $e) 
        {
            return ['status' => self::FAILURE, 'message' => "Erreur lors de la modification : " . $e->getMessage()];
        }
    }

    /*========================================================================================*/
	/*--------------------------------------- GETTER -----------------------------------------*/
	/*========================================================================================*/
	
	private function getAllPersons(Connection $connection, string $tableName): array
	{
		try 
		{
			$sql = "SELECT * FROM `$tableName` ORDER BY id DESC";
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


}
