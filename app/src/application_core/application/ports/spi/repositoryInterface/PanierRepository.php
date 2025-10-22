<?php
namespace abricotdepot\core\application\ports\spi\repositoryInterface;

use abricotdepot\core\domain\entities\Panier\Panier;

interface PanierRepository
{
    /**
     * Sauvegarde un panier en base
     */
    public function save(Panier $panier): void;

    /**
     * Cherche un panier par son ID
     */
    public function findById(string $id): ?Panier;

    /**
     * Récupère un panier avec tous ses items
     */
    public function getPanierWithItemsByPanierId(string $panierId): array;

    /**
     * Ajoute un item au panier
     */
    public function addItem(string $panierId, int $outilId, int $quantity = 1): void;
}
