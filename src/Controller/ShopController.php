<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Shop;
use App\Entity\Produit;
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
        $user = $this->getUser();

        // Cast the user to Utilisateur if it's an instance of Utilisateur
        if (!$user instanceof Utilisateur) {
            return $this->redirectToRoute('app_login');
        }

        if ($user->getShop()) {
            return $this->redirectToRoute('myshop');
        }

        // Create a new Shop entity
        $shop = new Shop();
        $shop->setUtilisateur($user); 

         $shop->setDateCreation((new \DateTime())->format('Y-m-d'));  // Set the current date   

        // Create a form to collect shop data
        $form = $this->createForm(ShopType::class, $shop);

        // Handle the form submission
        $form->handleRequest($request);

        // If the form is submitted and valid, save the shop and redirect to the shop page
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($shop);
            $em->flush();

            return $this->redirectToRoute('myshop');
        }

        return $this->render('shop/new.html.twig', [
            'shop' => $shop,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/shop/{shopId}', name: 'shop_show')]
    public function showShop(int $shopId, EntityManagerInterface $em): Response
    {
        // Get the current authenticated user
        $user = $this->getUser();

        // Cast the user to Utilisateur if it's an instance of Utilisateur
        if (!$user instanceof Utilisateur) {
            return $this->redirectToRoute('login');  // Redirect to login page if the user is not an instance of Utilisateur
        }

        $shop = $em->getRepository(Shop::class)->find($shopId);

        // Get the products for this shop
        $products = $shop->getProduits();

        // Render the show page for the shop
        return $this->render('shop/show.html.twig', [
            'shop' => $shop,
            'products' => $products,
        ]);
    }

    #[Route('/edit_shop/{shopId}', name: 'edit_shop')]
    public function editShop(int $shopId, Request $request, EntityManagerInterface $em): Response
    {
        // Get the current authenticated user
        $user = $this->getUser();
    
        if (!$user instanceof Utilisateur) {
            return $this->redirectToRoute('login');  // Redirect to login if user is not authenticated
        }
    
        // Find the shop that the user is trying to edit
        $shop = $em->getRepository(Shop::class)->find($shopId);
    
        // Check if the shop exists and belongs to the current user or if the user is an admin
        if (!$shop || ($shop->getUtilisateur() !== $user && !in_array('ROLE_ADMIN', $user->getRoles()))) {
            throw $this->createNotFoundException('Shop not found or you do not have permission to edit it.');
        }
    
        // Create the form to edit the shop
        $form = $this->createForm(ShopType::class, $shop);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Save the updated shop
            $em->persist($shop);
            $em->flush();
    
            // Redirect to the user's shop page after updating
            return $this->redirectToRoute('myshop');
        }
    
        return $this->render('shop/edit.html.twig', [
            'shop' => $shop,
            'form' => $form->createView(),
            'is_editing' => true, // We are editing an existing shop
        ]);
    }

    #[Route('/delete_shop/{shopId}', name: 'app_shop_delete')]
    public function deleteShop(int $shopId, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Utilisateur) {
            return $this->redirectToRoute('login'); 
        }

        $shop = $em->getRepository(Shop::class)->find($shopId);

        if (!$shop || ($shop->getUtilisateur() !== $user && !in_array('ROLE_ADMIN', $user->getRoles()))) {
            throw $this->createAccessDeniedException('You do not have permission to delete this shop.');
        }

        $em->remove($shop);
        $em->flush();

        return $this->redirectToRoute('home');
    }

    #[Route('/shop', name: 'shop_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $shops = $em->getRepository(Shop::class)->findAll();

        return $this->render('shop/index.html.twig', [
            'shops' => $shops,
        ]);
    }
    
}
