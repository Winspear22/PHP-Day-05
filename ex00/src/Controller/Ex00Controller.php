<?php

namespace App\Controller;

use Exception;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class Ex00Controller extends AbstractController
{
    
    /**
     * @Route("/ex00", name="ex00_index")
     */
    public function index(): Response
    {
        return $this->render('ex00/index.html.twig', [
            'controller_name' => 'Ex00Controller',
        ]);
    }

    /**
     * @Route("/ex00/create_table", name="ex00_create_table")
     */
    public function createTable(Connection $connection): Response
    {
        $tableName = 'users';
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(25) UNIQUE NOT NULL,
            name VARCHAR(25) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            enable BOOLEAN NOT NULL DEFAULT 1,
            birthdate DATETIME NOT NULL,
            address LONGTEXT NOT NULL
        )";

        try
        {
            if ($this->checkTableExistence($connection, $tableName) === true)
                $this->addFlash('info', "The table $tableName already exists and cannot be created again.");
            else
            {
                $connection->executeStatement($sql);
                $this->addFlash('success', "Success! The table $tableName was created!");
            }
        }
        catch (Exception $e)
        {
            $this->addFlash('danger', "Error, there was a problem in the table $tableName creation : " . $e->getMessage()); 
        }
        return $this->redirectToRoute('ex00_index');
    }

    private function checkTableExistence(Connection $connection, string $tableName): bool
    {
        try
        {
            $result = $connection->fetchOne("SHOW TABLES LIKE '$tableName'");
            return ($result !== false); // Si aucune table n'est trouvée, false est renvoyé (en cas de doute haha).
        }
        catch (Exception $e)
        {
            return false;
        }
    }
}
