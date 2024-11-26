<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Shop;
use App\Form\ShopType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    // Route to view the user's shop
    #[Route('/myshop', name: 'myshop')]
    public function myShop(): Response
    {
        // Get the current authenticated user
        $user = $this->getUser();

        // Cast the user to Utilisateur if it's an instance of Utilisateur
        if (!$user instanceof Utilisateur) {
            return $this->redirectToRoute('login');  // Redirect to login page if the user is not an instance of Utilisateur
        }

        // Check if the user has a shop
        if (!$user->getShop()) {
            return $this->redirectToRoute('create_shop');  // Redirect to create shop page if no shop exists
        }

        // Get the user's shop
        $shop = $user->getShop();

        // Render the shop details page
        return $this->render('shop/myshop.html.twig', [
            'shop' => $shop,
        ]);
    }

    // Route to create a shop
    #[Route('/create_shop', name: 'create_shop')]
    public function createShop(Request $request, EntityManagerInterface $em): Response
    {
        // Get the current authenticated user
        $user = $this->getUser();

        // Cast the user to Utilisateur if it's an instance of Utilisateur
        if (!$user instanceof Utilisateur) {
            return $this->redirectToRoute('app_login');  // Redirect to login page if the user is not an instance of Utilisateur
        }

        // If the user already has a shop, redirect to the 'myshop' page
        if ($user->getShop()) {
            return $this->redirectToRoute('myshop');
        }

        // Create a new Shop entity
        $shop = new Shop();
        $shop->setUtilisateur($user); 

         // Automatically set the current date for dateCreation
         $shop->setDateCreation((new \DateTime())->format('Y-m-d'));  // Set the current date   

        // Create a form to collect shop data
        $form = $this->createForm(ShopType::class, $shop);

        // Handle the form submission
        $form->handleRequest($request);

        // If the form is submitted and valid, save the shop and redirect to the shop page
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($shop);
            $em->flush();

            // Redirect to the newly created shop's page
            return $this->redirectToRoute('myshop');
        }

        // Render the form for creating the shop
        return $this->render('shop/new.html.twig', [
            'shop' => $shop,
            'form' => $form->createView(),
        ]);
    }
}
