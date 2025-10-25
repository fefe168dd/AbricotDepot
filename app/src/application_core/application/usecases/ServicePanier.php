<?php
namespace abricotdepot\core\application\usecases;

use abricotdepot\core\application\ports\spi\repositoryInterface\PanierRepository;
use abricotdepot\core\application\ports\api\dto\PanierDTO;
use abricotdepot\core\domain\entities\Panier\Panier;

class ServicePanier 
{
    private PanierRepository $panierRepository;

    public function __construct(PanierRepository $panierRepository)
    {
        $this->panierRepository = $panierRepository;
    }

    public function getAllPaniers(): array
    {
        $paniers = $this->panierRepository->getAllPaniers();
        return array_map(fn(Panier $p) => new PanierDTO($p), $paniers);
    }

    public function savePanier(Panier $panier): void
    {
        $this->panierRepository->savePanier($panier);
    }

    public function findById(string $id): ?PanierDTO
    {
        $panier = $this->panierRepository->findById($id);
        return $panier ? new PanierDTO($panier) : null;
    }

    public function getPanierItemsByUserId(string $userId): array
    {
        $paniers = $this->panierRepository->getPanierItemsByUserId($userId);
        return array_map(fn(Panier $p) => new PanierDTO($p), $paniers);
    }

    public function getPanierItemsByUserIdAndOutilId(string $userId, string $outilId): array
    {
        $paniers = $this->panierRepository->getPanierItemsByUserIdAndOutilId($userId, $outilId);
        return array_map(fn(Panier $p) => new PanierDTO($p), $paniers);
    }
}