<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/produit')]
final class ProduitController extends AbstractController
{
    #[Route(name: 'app_produit_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $produits = $entityManager
            ->getRepository(Produit::class)
            ->findAll();

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get the current authenticated user
        $user = $this->getUser();
    
        // Cast the user to Utilisateur if it's an instance of Utilisateur
        if (!$user instanceof Utilisateur) {
            return $this->redirectToRoute('app_login');  // Redirect to login page if the user is not authenticated
        }
    
        // Create a new Produit (Product) entity
        $produit = new Produit();
    
        // Associate the product with the user's shop
        if ($user->getShop()) {
            $produit->setShop($user->getShop());  // Set the shop to the current user's shop
        } else {
            // Handle the case where the user doesn't have a shop (optional)
            return $this->redirectToRoute('create_shop');  // Redirect to create shop page if no shop exists
        }

        $produit->setDateCreation((new \DateTime())->format('Y-m-d'));  // Set the current date   
    
        // Create the form to collect product data
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
    
        // Handle form submission
        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the product
            $entityManager->persist($produit);
            $entityManager->flush();
    
            // Redirect to the product listing page or a product details page
            return $this->redirectToRoute('myshop');
        }
    
        // Render the form for creating the product
        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }
    

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}
