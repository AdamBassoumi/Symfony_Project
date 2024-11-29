<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/utilisateur')]
final class UtilisateurController extends AbstractController
{

    private $passwordHasher;
    private $tokenStorage;
    private Security $security;

    public function __construct(UserPasswordHasherInterface $passwordHasher, TokenStorageInterface $tokenStorage)
    {
        $this->passwordHasher = $passwordHasher;
        $this->tokenStorage = $tokenStorage;
    }


    #[Route(name: 'app_utilisateur_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $utilisateurs = $entityManager
            ->getRepository(Utilisateur::class)
            ->findAll();

        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }

    #[Route('/new', name: 'app_utilisateur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($utilisateur);
            $entityManager->flush();

            return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('utilisateur/new.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_utilisateur_show', methods: ['GET'])]
    public function show(Utilisateur $utilisateur): Response
    {
        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_utilisateur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {

        $user = $this->getUser();

        // Check if the authenticated user is the one being edited
        if ($user !== $utilisateur) {
            // Throw an AccessDeniedException if the users don't match
            throw new AccessDeniedException('You are not allowed to edit this profile.');
        }
        
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
    
            $oldPassword = $form->get('oldPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();
            $confirmPassword = $form->get('confirmMdp')->getData();
    
            // Validate old password
            if (!$passwordHasher->isPasswordValid($utilisateur, $oldPassword)) {
                $this->addFlash('error', 'The old password is incorrect.');
                return $this->render('utilisateur/edit.html.twig', [
                    'form' => $form->createView(),
                    'utilisateur' => $utilisateur,
                ]);
            }
    
            // Validate new password and confirm password
            if ($newPassword && $newPassword !== $confirmPassword) {
                $this->addFlash('error', 'The new passwords do not match.');
                return $this->render('utilisateur/edit.html.twig', [
                    'form' => $form->createView(),
                    'utilisateur' => $utilisateur,
                ]);
            }
    
            // Update password if provided
            if ($newPassword) {
                $hashedPassword = $passwordHasher->hashPassword($utilisateur, $newPassword);
                $utilisateur->setMdp($hashedPassword);
            }
    
            $entityManager->persist($utilisateur);
            $entityManager->flush();
    
            return $this->redirectToRoute('home');
        }
    
        return $this->render('utilisateur/edit.html.twig', [
            'form' => $form->createView(),
            'utilisateur' => $utilisateur,
        ]);
    }
    

    #[Route('/{id}', name: 'app_utilisateur_delete', methods: ['POST'])]
    public function delete(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($utilisateur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
    }
}
