<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface; // Import the interface

#[ORM\Entity]
#[ORM\Table(name: "utilisateur")]
class Utilisateur implements UserInterface,PasswordAuthenticatedUserInterface
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


    #[ORM\OneToOne(mappedBy: "utilisateur", cascade: ["persist", "remove"] , targetEntity: Shop::class)]
    private ?Shop $shop = null;

    private array $roles = [];

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

    public function getRoles(): array
    {
        // Ensure roles are always returned as an array
        $roles = $this->roles;
    
        // If no roles are set, assign the default 'ROLE_USER'
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';  // Default role
        }
    
        return $roles; // Return roles as an array
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    // Optional: Implement getUserIdentifier() if needed (returns the user's unique identifier, typically email)
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // If you store sensitive data in the entity, clear it here
        // e.g. $this->plainPassword = null;
    }
}
