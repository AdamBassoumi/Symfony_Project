<?php

namespace App\Controller;

use App\Entity\Shop;
use App\Form\ShopType;
use App\Repository\ShopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    #[Route('/create_shop', name: 'create_shop')]
    public function createShop(Request $request, ShopRepository $shopRepository): Response
    {
        // Get the currently authenticated user
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('login');
        }

        // Create a new Shop entity
        $shop = new Shop();
        $shop->setUtilisateur($user);

        // Create the form to fill in the shop details
        $form = $this->createForm(ShopType::class, $shop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the shop
            $shopRepository->save($shop, true);

            // After shop creation, you can redirect to the shop page or home page
            return $this->redirectToRoute('home');
        }

        return $this->render('shop/create_shop.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

