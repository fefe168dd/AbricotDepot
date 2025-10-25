<?php

namespace abricotdepot\core\application\ports\spi\repositoryInterface;

use abricotdepot\core\domain\entities\Panier\Panier;

interface PanierRepository
{
    //Récupère tous les paniers
    public function getAllPaniers(): array;

    //Sauvegarde un panier en base
    public function savePanier(Panier $panier): void;

    //Cherche un panier par son ID
    public function findById(string $id): ?Panier;

    //Récupère un panier avec tous ses items
    public function getPanierItemsByUserId(string $userId): array;

    //Ajoute un item au panier
    public function getPanierItemsByUserIdAndOutilId(string $userId, string $outilId): array;
}
