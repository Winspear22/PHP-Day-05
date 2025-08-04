<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class Ex09Controller extends AbstractController
{
    #[Route('/ex09', name: 'app_ex09')]
    public function index(): Response
    {
        return $this->render('ex09/index.html.twig', [
            'controller_name' => 'Ex09Controller',
        ]);
    }
}
