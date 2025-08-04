<?php

namespace App\Controller;

use Throwable;
use App\Entity\Person;
use App\Entity\Address;
use App\Entity\BankAccount;
use App\Service\TablesDeleteService;
use App\Service\TablesMigrationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class Ex09Controller extends AbstractController
{
    #[Route('/ex09', name: 'ex09_index')]
    public function index(): Response
    {
        return $this->render('ex09/index.html.twig', [
            'controller_name' => 'Ex09Controller',
        ]);
    }

    /**
     * @Route("/ex09/drop_tables", name="ex09_drop_tables", methods={"POST"})
     */
    public function dropTables(TablesDeleteService $tablesDeleteService): Response
    {
        $entities = [
            Person::class,
            Address::class,
            BankAccount::class,
        ];
        $result = $tablesDeleteService->dropTables($entities);
        [$type, $msg] = explode(':', $result, 2);
        $this->addFlash($type, $msg);

        return $this->redirectToRoute('ex09_index');
    }

    /**
     * @Route("/ex09/migrate", name="ex09_migrate", methods={"POST"})
     */
    public function migrate(TablesMigrationService $migrationRunnerService): Response
    {
        try
        {
            $output = $migrationRunnerService->migrate();
            $this->addFlash('success', 'Success! Migration succeeded.');
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error, migration failed: ' . $e->getMessage());
        }
        return $this->redirectToRoute('ex09_index');
    }

    /**
     * @Route("/ex09/update_schema", name="ex09_update_schema", methods={"POST"})
     */
    public function updateSchema(TablesMigrationService $schemaUpdateService): Response
    {
        try
        {
            $output = $schemaUpdateService->updateSchema();
            $this->addFlash($output['type'], $output['message']);
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error, schema update failed: ' . $e->getMessage());
        }
        return $this->redirectToRoute('ex09_index');
    }

}
