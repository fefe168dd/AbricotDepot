<?php
namespace abricotdepot\core\domain\entities\Outil;
use abricotdepot\core\domain\entities\Outil\Categorie;

class Outil
{
    private string $id;
    private string $nom;
    private string $description;
    private float $prix;
    private string $imageUrl;
    private Categorie $categorie;

    public function __construct(string $id, string $nom, string $description, float $prix, string $imageUrl, Categorie $category)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->description = $description;
        $this->prix = $prix;
        $this->imageUrl = $imageUrl;
        $this->categorie = $category;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getprix(): float
    {
        return $this->prix;
    }

    public function getCategorie(): Categorie
    {
        return $this->categorie;
    }
    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }
}