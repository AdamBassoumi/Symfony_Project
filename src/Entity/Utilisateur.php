<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface; // Import the interface

#[ORM\Entity]
#[ORM\Table(name: "utilisateur")]
class Utilisateur implements PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $mdp = null;

    #[ORM\Column(type: "boolean")]
    private bool $hasShop = false;

    #[ORM\OneToOne(mappedBy: "utilisateur", cascade: ["persist", "remove"])]
    private ?Shop $shop = null;

    // Getters and Setters for each property

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getMdp(): ?string
    {
        return $this->mdp;
    }

    public function setMdp(string $mdp): self
    {
        $this->mdp = $mdp;
        return $this;
    }

    public function getHasShop(): bool
    {
        return $this->hasShop;
    }

    public function setHasShop(bool $hasShop): self
    {
        $this->hasShop = $hasShop;
        return $this;
    }

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): self
    {
        $this->shop = $shop;
        return $this;
    }

    // Implement the getPassword() method from PasswordAuthenticatedUserInterface
    public function getPassword(): string
    {
        return $this->mdp;  // Return the password field for hashing
    }

    // Optional: Implement getRoles() if needed (it can return an empty array or default roles)
    public function getRoles(): array
    {
        return ['ROLE_USER'];  // Or any roles you'd like the user to have
    }

    // Optional: Implement getUserIdentifier() if needed (returns the user's unique identifier, typically email)
    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
