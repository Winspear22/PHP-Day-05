<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class Ex08Controller extends AbstractController
{
    #[Route('/ex08', name: 'app_ex08')]
    public function index(): Response
    {
        return $this->render('ex08/index.html.twig', [
            'controller_name' => 'Ex08Controller',
        ]);
    }
}
