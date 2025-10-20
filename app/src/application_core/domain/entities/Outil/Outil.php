<?php
namespace App\core\domain\entities\Outil;
use App\core\domain\entities\Outil\Categorie;

class Outil
{
    private string $id;
    private string $nom;
    private string $description;
    private float $prix;
    private string $imageUrl;
    private Categorie $category;

    public function __construct(string $id, string $nom, string $description, float $prix, string $imageUrl, Categorie $category)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->description = $description;
        $this->prix = $prix;
        $this->imageUrl = $imageUrl;
        $this->category = $category;
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

    public function getCategory(): Categorie
    {
        return $this->category;
    }
    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }
}