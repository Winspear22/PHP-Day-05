<?php

namespace App\Controller;

use App\Entity\Ex10OrmItem;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Ex10Controller extends AbstractController
{
    const SUCCESS = 0;
	const FAILURE = 1;
	const DOES_NOT_EXIST = 2;

    /**
    * @Route("/ex10", name="ex10_index")
     */
    public function index(Connection $connection, EntityManagerInterface $em): Response
    {
        try
        {
            $sqlItems = $connection->fetchAllAssociative('SELECT * FROM ex10_sql_items');
        } 
        catch (\Exception $e)
        {
            $this->addFlash('error', 'Erreur SQL : ' . $e->getMessage());
            $sqlItems = [];
        }

        try 
        {
            $ormItems = $em->getRepository(Ex10OrmItem::class)->findAll();
        } 
        catch (\Exception $e)
        {
            $this->addFlash('error', 'Erreur ORM : ' . $e->getMessage());
            $ormItems = [];
        }

        return $this->render('index.html.twig', [
            'sqlItems' => $sqlItems,
            'ormItems' => $ormItems
        ]);
    }


/**
 * @Route("/ex10/import", name="ex10_import", methods={"POST"})
 */
public function importFromFile(Connection $connection, EntityManagerInterface $em, Request $request): Response
{
    try 
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/data.txt';
        if (!file_exists($filePath) || !is_readable($filePath))
        {
            $this->addFlash('error', 'Fichier introuvable ou illisible.');
            return $this->redirectToRoute('ex10_index');
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $nbLines = count($lines);

        if ($nbLines === 0)
        {
            $this->addFlash('error', 'Le fichier ne contient aucune ligne.');
            return $this->redirectToRoute('ex10_index');
        }

        if ($nbLines > 10)
        {
            $this->addFlash('error', 'Le fichier ne doit pas contenir plus de 10 lignes.');
            return $this->redirectToRoute('ex10_index');
        }

        $validEntries = [];
        $errors = [];
        $lineNum = 1;

        foreach ($lines as $line) {
            // Check si la ligne contient exactement 1 virgule
            if (substr_count($line, ',') !== 1) 
            {
                $errors[] = "Ligne $line : format incorrect.";
            } 
            else 
            {
                [$name, $value] = array_map('trim', explode(',', $line, 2));
                // Vérifie que les deux champs sont non vides
                if ($name === '' || $value === '') {
                    $errors[] = "Ligne $line : nom ou valeur vide.";
                } 
                else 
                {
                    $validEntries[] = [$name, $value];
                }
            }
            $lineNum++;
        }

        if (empty($validEntries)) 
        {
            $this->addFlash('error', 'Aucune ligne valide dans le fichier. ' . (!empty($errors) ? implode(' ', $errors) : ''));
            return $this->redirectToRoute('ex10_index');
        }

        if (!empty($errors)) 
        {
            $this->addFlash('error', 'Des lignes sont invalides : ' . implode(' ', $errors) . ' Rien n\'a été importé.');
            return $this->redirectToRoute('ex10_index');
        }

        // Si tout est bon : purge, puis import
        $connection->executeStatement('DELETE FROM ex10_sql_items');
        $em->createQuery('DELETE FROM App\Entity\Ex10OrmItem')->execute();

        $insertedSql = 0;
        $insertedOrm = 0;

        foreach ($validEntries as [$name, $value])
        {
            $connection->executeStatement(
                'INSERT INTO ex10_sql_items (name, value) VALUES (?, ?)',
                [$name, $value]
            );
            $insertedSql++;

            $ormItem = new Ex10OrmItem();
            $ormItem->setName($name);
            $ormItem->setValue($value);
            $em->persist($ormItem);
            $insertedOrm++;
        }
        $em->flush();

        $this->addFlash('message', "Import SQL ($insertedSql), ORM ($insertedOrm) réussi.");
    } 
    catch (\Exception $e) 
    {
        $this->addFlash('error', 'Erreur lors de l\'import : ' . $e->getMessage());
    }
    return $this->redirectToRoute('ex10_index');
}



    /**
     * @Route("/ex10/create-sql-table", name="ex10_create_sql_table", methods={"POST"})
     */
    public function createSqlTable(Connection $connection)
    {
        try 
        {
            $schemaManager = $connection->createSchemaManager();
            $tables = $schemaManager->listTableNames();

            if (in_array('ex10_sql_items', $tables))
                $this->addFlash('error', 'La table SQL existe déjà.');
            else 
            {
                $sql = "CREATE TABLE ex10_sql_items (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    value VARCHAR(255) NOT NULL
                )";
                $connection->executeStatement($sql);
                $this->addFlash('message', 'Table SQL créée avec succès.');
            }
        } 
        catch (\Exception $e) 
        {
            $this->addFlash('error', 'Erreur lors de la création de la table : ' . $e->getMessage());
        }
        return $this->redirectToRoute('ex10_index');
}
    /**
    * @Route("/ex10/clear", name="ex10_clear", methods={"POST"})
    */
    public function clearDatabase(Connection $connection, EntityManagerInterface $em)
    {
        try 
        {
            $connection->executeStatement('DELETE FROM ex10_sql_items');
            $em->createQuery('DELETE FROM App\Entity\Ex10OrmItem')->execute();

            $this->addFlash('message', 'Les deux tables ont été vidées !');
        } 
        catch (\Exception $e)
        {
            $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
        return $this->redirectToRoute('ex10_index');
    }


}
