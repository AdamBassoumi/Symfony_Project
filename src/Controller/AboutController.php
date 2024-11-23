<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutController extends AbstractController
{
    #[Route('/about', name: 'about_page')]
    public function index(): Response
    {
        return $this->render('about/index.html.twig', [
            'title' => 'About',
            'description' => 'Welcome to the "About" page of our VisualCraft project.',

        ]);
    }
}
