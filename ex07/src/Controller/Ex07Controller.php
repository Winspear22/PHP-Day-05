<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class Ex07Controller extends AbstractController
{
    #[Route('/ex07', name: 'app_ex07')]
    public function index(): Response
    {
        return $this->render('ex07/index.html.twig', [
            'controller_name' => 'Ex07Controller',
        ]);
    }
}
