<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Ex02Controller extends AbstractController
{
	const SUCCESS = 0;
	const FAILURE = 1;
	const DOES_NOT_EXIST = 2;

	/**
	 * @Route("/ex02", name="ex02_index")
	 */
	public function index(Request $request, Connection $connection): Response
	{
		$tableName = "users";

		$tableStatus = $this->tableExistenceCheck($tableName, $connection);
		if ($tableStatus['status'] === self::DOES_NOT_EXIST) 
		{
			$creationResult = $this->createTable($tableName, $connection);
			$this->addFlash('notice', $creationResult['message']);
		}

		$form = $this->buildPersonForm();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) 
		{
			$message = $this->handlePersonFormSubmission($form, $connection, $tableName);
			if ($message)
				$this->addFlash('notice', $message);
			return $this->redirectToRoute('ex02_index');
		}

		$users = $this->getAllUsers($connection, $tableName);

		return $this->render('index.html.twig', [
			'form' => $form->createView(),
			'users' => $users,
		]);
	}

	/*========================================================================================*/
	/*---------------------------------- CREATE THE TABLE ------------------------------------*/
	/*========================================================================================*/
	private function tableExistenceCheck(string $tableName, Connection $connection): array
    {
		try
		{
			$doesTableExists = $connection->executeQuery("SHOW TABLES LIKE '$tableName'")->rowCount();
			if ($doesTableExists > 0)
				return (['status' => self::SUCCESS, 'message' => "La table $tableName existe déjà."]); // La table existe.
			return (['status' => self::DOES_NOT_EXIST]); // La table n'existe pas.
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

	/*========================================================================================*/
	/*----------------------------------- CREATE THE FORM ------------------------------------*/
	/*========================================================================================*/
	private function buildPersonForm(): \Symfony\Component\Form\FormInterface
	{
		return $this->createFormBuilder(null, [
			'method' => 'POST',
		])
			->add('username', TextType::class)
			->add('name', TextType::class)
			->add('email', EmailType::class)
			->add('enable', CheckboxType::class, ['required' => false])
			->add('birthdate', DateType::class, ['widget' => 'single_text'])
			->add('address', TextareaType::class)
			->add('submit', SubmitType::class, ['label' => 'Ajouter l’utilisateur'])
			->getForm();
	}
	private function handlePersonFormSubmission($form, Connection $connection, string $tableName): ?string
	{
		if ($form->isSubmitted() && $form->isValid()) 
		{
			$data = $form->getData();
			$birthdate = $data['birthdate'] instanceof \DateTimeInterface
				? $data['birthdate']->format('Y-m-d H:i:s')
				: $data['birthdate'];
			$result = $this->insertPerson([
				'username' => $data['username'],
				'name' => $data['name'],
				'email' => $data['email'],
				'enable' => $data['enable'] ?? 0,
				'birthdate' => $birthdate,
				'address' => $data['address'],
			], $connection, $tableName);

			return $result['message'];
		}
		return null;
	}

	/*========================================================================================*/
	/*--------------------------------------- GETTER -----------------------------------------*/
	/*========================================================================================*/
	
	private function getAllUsers(Connection $connection, string $tableName): array
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

	/*========================================================================================*/
	/*--------------------------------------- SETTER -----------------------------------------*/
	/*========================================================================================*/

	private function insertPerson(array $data, Connection $connection, string $tableName): array
	{
		try 
		{
			$sql = "INSERT INTO `$tableName` (username, name, email, enable, birthdate, address)
					VALUES (:username, :name, :email, :enable, :birthdate, :address)";
			$connection->executeStatement($sql, [
				'username' => $data['username'],
				'name' => $data['name'],
				'email' => $data['email'],
				'enable' => !empty($data['enable']) ? 1 : 0,
				'birthdate' => $data['birthdate'],
				'address' => $data['address'],
			]);
			return ['status' => self::SUCCESS, 'message' => "Insertion réussie !"];
		} 
		catch (\Exception $e) 
		{
			return ['status' => self::FAILURE, 'message' => "Erreur lors de l'insertion : " . $e->getMessage()];
		}
	}
}
