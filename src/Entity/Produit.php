<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: "produit")]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string")]
    private string $name;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "float")]
    private float $prix;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $fichier = null;

    #[ORM\Column(type: "string")]
    private string $dateCreation;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: "category_id", referencedColumnName: "id", nullable: true)]
    private ?Category $category;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: "shop_id", referencedColumnName: "id", nullable: false)]
    private Shop $shop;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: Commentaire::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $commentaires;

    // Getters and setters for all properties

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function getFichier(): ?string
    {
        return $this->fichier;
    }

    public function setFichier(string $fichier): self
    {
        $this->fichier = $fichier;
        return $this;
    }

    public function getDateCreation(): ?string
    {
        return $this->dateCreation;
    }

    public function setDateCreation(string $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
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

    public function getCommentaires()
    {
        return $this->commentaires;
    }

    public function setCommentaires($commentaires): self
    {
        $this->commentaires = $commentaires;
        return $this;
    }
}
