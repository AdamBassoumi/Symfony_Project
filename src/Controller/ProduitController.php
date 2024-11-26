<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    
    #[Route('/show/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit, Security $security): Response
    {
        $user = $security->getUser();
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
            'authenticatedUser' => $user,
        ]);
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        
        // Ensure that the user is authenticated and is an instance of Utilisateur
        if (!$user instanceof Utilisateur) {
            return $this->redirectToRoute('login'); 
        }
        
        $produit = new Produit();
        
        // Associate the product with the user's shop
        if ($user->getShop()) {
            $produit->setShop($user->getShop());
        } else {
            return $this->redirectToRoute('create_shop'); 
        }
        
        $produit->setDateCreation((new \DateTime())->format('Y-m-d'));
    
        // Create the form to collect product data
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
        
        // Check if the form is submitted
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('fichier')->getData();  // Get the uploaded file
            
            // Check if a file was uploaded
            if ($file) {
                // Get the original file name
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename); // Slugify the original filename
                
                // Generate a new filename (unique)
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension(); // Add unique identifier to filename
                
                // Handle file upload
                try {
                    // Move the uploaded file to the specified directory
                    $file->move(
                        $this->getParameter('product_images_directory'), // Path defined in services.yaml
                        $newFilename
                    );
                    
                    // Set the filename of the uploaded file in the entity
                    $produit->setFichier($newFilename);
                } catch (FileException $e) {
                    // Handle file upload error
                    $this->addFlash('error', 'There was an issue uploading the image.');
                    return $this->redirectToRoute('app_produit_new');
                }
            } else {
                $this->addFlash('error', 'No file uploaded.');
                return $this->redirectToRoute('app_produit_new');
            }
    
            $entityManager->persist($produit);
            $entityManager->flush();
    
            return $this->redirectToRoute('myshop');
        }
    
        // If the form is not valid, show form errors
        if ($form->isSubmitted() && !$form->isValid()) {
            $errors = $form->getErrors(true, false);
            foreach ($errors as $error) {
                dump($error->getMessage());
            }
        }
    
        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        
        // Check if the authenticated user is either the owner of the shop or an admin
        if (!$this->canEditOrDelete($user, $produit)) {
            // If the user is not allowed to edit, redirect them or show an error
            $this->addFlash('error', 'You do not have permission to edit this product.');
            return $this->redirectToRoute('home');
        }
    
        // Save the current image (if any) before the form is submitted, to prevent it from being lost
        $currentImage = $produit->getFichier();
    
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('fichier')->getData();  // Get the uploaded file
    
            if ($file) {
                // If a new file was uploaded, handle the upload process
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename); // Slugify the original filename
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension(); // Add unique identifier to filename
                
                try {
                    // Move the uploaded file to the specified directory
                    $file->move(
                        $this->getParameter('product_images_directory'), // Path defined in services.yaml
                        $newFilename
                    );
                    
                    // Set the filename of the uploaded file in the entity
                    $produit->setFichier($newFilename);
    
                    // Delete the old image if it exists (important to avoid orphaned files)
                    if ($currentImage) {
                        $oldImagePath = $this->getParameter('product_images_directory') . '/' . $currentImage;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);  // Delete the old image file
                        }
                    }
                } catch (FileException $e) {
                    // Handle file upload error
                    $this->addFlash('error', 'There was an issue uploading the image.');
                    return $this->redirectToRoute('app_produit_edit', ['id' => $produit->getId()]);
                }
            } else {
                // If no new file is uploaded, keep the current image
                $produit->setFichier($currentImage);
            }
    
            $entityManager->flush();
    
            return $this->redirectToRoute('myshop', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        // Check if the authenticated user is either the owner of the shop or an admin
        if (!$this->canEditOrDelete($user, $produit)) {
            // If the user is not allowed to delete, redirect them or show an error
            $this->addFlash('error', 'You do not have permission to delete this product.');
            return $this->redirectToRoute('home');
        }

        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('myshop', [], Response::HTTP_SEE_OTHER);
    }

    // Helper method to check if the user can edit or delete the product
    private function canEditOrDelete(Utilisateur $user, Produit $produit): bool
    {
        // Check if the user is an ADMIN
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Check if the product's shop belongs to the current authenticated user
        if ($produit->getShop() && $produit->getShop()->getUtilisateur() === $user) {
            return true;
        }

        return false;
    }
}
