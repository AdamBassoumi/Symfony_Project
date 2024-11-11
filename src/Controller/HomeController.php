<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProduitRepository;


class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(ProduitRepository $produitRepository): Response
    {
        // Fetch all products
        $produits = $produitRepository->findAll();

        // Render the homepage with the products
        return $this->render('home/index.html.twig', [
            'produits' => $produits,
        ]);
    }
}