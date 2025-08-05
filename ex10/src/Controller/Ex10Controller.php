<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class Ex10Controller extends AbstractController
{
    /**
     * @Route("/ex10", name="ex10_index")
     */
    public function index(): Response
    {
        return $this->render('ex10/index.html.twig', [
            'controller_name' => 'Ex10Controller',
        ]);
    }
}
