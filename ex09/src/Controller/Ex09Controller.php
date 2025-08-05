<?php

namespace App\Controller;

use Throwable;
use App\Service\TablesMigrationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class Ex09Controller extends AbstractController
{
    /**
     * @Route("/ex09", name="ex09_index")
     */
    public function index(): Response
    {
        return $this->render('ex09/index.html.twig');
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

    /**
     * @Route("/ex09/show_snippet", name="ex09_show_snippet")
     */
    public function showSnippet(TablesMigrationService $schemaUpdateService): Response
    {
        $snippet = file_get_contents($schemaUpdateService->getSnippetFilePath());
        return $this->render('ex09/snippet.html.twig', ['snippet' => $snippet]);
    }
}
