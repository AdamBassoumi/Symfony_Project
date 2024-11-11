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

class SecurityController extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;

    // Constructor to inject UserPasswordHasherInterface
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    // Signup route
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

            // Save the user
            $userRepository->save($utilisateur, true);

            // Redirect to login after successful signup
            return $this->redirectToRoute('login');
        }

        return $this->render('security/signup.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Login route (just rendering the login form)
    #[Route('/login', name: 'login')]
    public function login(): Response
    {
        return $this->render('security/login.html.twig');
    }

    // Logout route (handled automatically by Symfony)
    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        // Symfony automatically handles logout
    }
}
