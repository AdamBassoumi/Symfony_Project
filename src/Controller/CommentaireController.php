<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Produit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commentaire')]
final class CommentaireController extends AbstractController
{
    #[Route(name: 'app_commentaire_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $commentaires = $entityManager
            ->getRepository(Commentaire::class)
            ->findAll();

        return $this->render('commentaire/index.html.twig', [
            'commentaires' => $commentaires,
        ]);
    }

    #[Route('/new/{id}', name: 'create_commentaire', methods: ['POST'])]
    public function createCommentaire(Produit $produit, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('login');
        }
    
        $text = $request->request->get('text');
        $note = (int) $request->request->get('note');
    
        $commentaire = new Commentaire();
        $commentaire->setText($text)
            ->setNote($note)
            ->setProduit($produit)
            ->setDate((new \DateTime())->format('Y-m-d H:i:s'))
            ->setUname($this->getUser()->getEmail()); // Use 'uname' from the user (email)
    
        $entityManager->persist($commentaire);
        $entityManager->flush();
    
        return $this->redirectToRoute('app_produit_show', ['id' => $produit->getId()]);
    }

    // #[Route('/{id}', name: 'app_commentaire_show', methods: ['GET'])]
    // public function show(Commentaire $commentaire): Response
    // {
    //     return $this->render('commentaire/show.html.twig', [
    //         'commentaire' => $commentaire,
    //     ]);
    // }

    #[Route('/editt/{id}', name: 'edit_commentairee', methods: ['POST'])]
    public function editCommentaire(Commentaire $commentaire, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('login');
        }

        if ($commentaire->getUname() !== $this->getUser()->getEmail()) {
            throw $this->createAccessDeniedException('You are not authorized to edit this comment.');
        }

        $text = $request->request->get('text');
        $note = (int) $request->request->get('note');

        // Update the comment
        $commentaire->setText($text)
            ->setNote($note)
            ->setDate((new \DateTime())->format('Y-m-d H:i:s'));

        $entityManager->flush();

        return $this->redirectToRoute('app_produit_show', ['id' => $commentaire->getProduit()->getId()]);
    }

    #[Route('/{id}', name: 'app_commentaire_delete', methods: ['POST'])]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have permission to delete this comment.');
        }
    
        // Check CSRF token validity
        if ($this->isCsrfTokenValid('delete'.$commentaire->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commentaire);
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('app_commentaire_index', [], Response::HTTP_SEE_OTHER);
    }
}
