<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Get the current authenticated user
        $user = $this->getUser();
        
        // Ensure that the user is authenticated and is an instance of Utilisateur
        if (!$user instanceof Utilisateur) {
            return $this->redirectToRoute('app_login');  // Redirect to login if the user is not authenticated
        }
        
        // Create a new Produit entity
        $produit = new Produit();
        
        // Associate the product with the user's shop
        if ($user->getShop()) {
            $produit->setShop($user->getShop());  // Set the shop for the product
        } else {
            return $this->redirectToRoute('create_shop');  // Redirect to create shop if no shop exists
        }
        
        // Set the current date for the product creation
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
                // If no file is uploaded
                $this->addFlash('error', 'No file uploaded.');
                return $this->redirectToRoute('app_produit_new');
            }
    
            // Persist the new product entity to the database
            $entityManager->persist($produit);
            $entityManager->flush();
    
            // Redirect to the shop's product listing page or product details page
            return $this->redirectToRoute('myshop');
        }
    
        // If the form is not valid, show form errors
        if ($form->isSubmitted() && !$form->isValid()) {
            $errors = $form->getErrors(true, false);
            foreach ($errors as $error) {
                dump($error->getMessage()); // Log form errors
            }
        }
    
        // Render the form to create the product
        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
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
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}
