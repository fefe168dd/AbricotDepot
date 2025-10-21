<?php 
namespace abricotdepot\core\application\ports\api\dto;

use abricotdepot\core\domain\entities\Outil\Categorie;
use abricotdepot\core\domain\entities\Outil\Outil;
class OutilDTO 
{
    public string $id;
    public string $nom;
    public string $description;
    public float $prix;
    public string $imageUrl;
    public array $categorie;
    public function __construct(Outil $outil)
    {
        $this->id = $outil->getId();
        $this->nom = $outil->getNom();
        $this->description = $outil->getDescription();
        $this->prix = $outil->getprix();
        $this->imageUrl = $outil->getImageUrl();
        $cat = $outil->getCategorie();
        $this->categorie = [
            'id' => $cat->getId(),
            'nom' => $cat->getNom(),
        ];
    }
}