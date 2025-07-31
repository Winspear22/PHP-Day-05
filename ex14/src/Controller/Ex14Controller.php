<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class Ex14Controller extends AbstractController
{
    #[Route('/ex14', name: 'app_ex14')]
    public function index(): Response
    {
        return $this->render('ex14/index.html.twig', [
            'controller_name' => 'Ex14Controller',
        ]);
    }
}
