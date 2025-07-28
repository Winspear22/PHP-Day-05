<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Ex01Controller extends AbstractController
{
    /**
     * @Route("/ex01", name="ex01_index")
     */
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) 
        {
            try 
            {
				$schemaManager = $em->getConnection()->createSchemaManager();
				$schemaTool = new SchemaTool($em);
				$metadata = [$em->getClassMetadata(User::class)];
                if ($schemaManager->tablesExist(['user']))
                    $this->addFlash('notice', "La table 'user' existe déjà.");
                else 
                {
                    $schemaTool->createSchema($metadata);
                    $this->addFlash('notice', "La table 'user' a été créée avec succès.");
                }
            } 
            catch (\Exception $e) 
            {
                $this->addFlash('error', "Erreur lors de la création de la table : " . $e->getMessage());
            }
            return $this->redirectToRoute('ex01_index');
        }

        return $this->render('index.html.twig');
    }
}