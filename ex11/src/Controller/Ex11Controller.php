<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class Ex11Controller extends AbstractController
{
    #[Route('/ex11', name: 'app_ex11')]
    public function index(): Response
    {
        return $this->render('ex11/index.html.twig', [
            'controller_name' => 'Ex11Controller',
        ]);
    }
}
