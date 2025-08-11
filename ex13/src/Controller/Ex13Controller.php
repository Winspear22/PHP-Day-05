<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class Ex13Controller extends AbstractController
{
    #[Route('/ex13', name: 'app_ex13')]
    public function index(): Response
    {
        return $this->render('ex13/index.html.twig', [
            'controller_name' => 'Ex13Controller',
        ]);
    }
}
