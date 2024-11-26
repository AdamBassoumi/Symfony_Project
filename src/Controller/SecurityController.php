<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationType;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;
    private Security $security;

    // Constructor to inject UserPasswordHasherInterface and Security component
    public function __construct(UserPasswordHasherInterface $passwordHasher, TokenStorageInterface $tokenStorage)
    {
        $this->passwordHasher = $passwordHasher;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/signup', name: 'signup')]
    public function signup(Request $request, UtilisateurRepository $userRepository): Response
    {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(RegistrationType::class, $utilisateur);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the password using UserPasswordHasherInterface
            $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, $utilisateur->getMdp());
            $utilisateur->setMdp($hashedPassword);
            $utilisateur->setRoles(['ROLE_USER']); // Ensure roles are passed as an array

            // Save the user
            $userRepository->save($utilisateur, true);

            // Redirect to home or dashboard after successful signup
            return $this->redirectToRoute('home');
        }

        return $this->render('security/signup.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Check if the user is already logged in
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // Get the login error (if any)
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        // Symfony automatically handles logout
    }
}

