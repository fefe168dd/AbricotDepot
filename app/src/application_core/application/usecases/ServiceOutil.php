<?php
namespace App\core\application\usecases;

use App\core\application\ports\spi\repositoryInterface\OutilRepository;
use App\core\application\ports\api\dto\OutilDTO;

class ServiceOutil 
{
    private OutilRepository $outilRepository;

    public function __construct(OutilRepository $outilRepository)
    {
        $this->outilRepository = $outilRepository;
    }

    public function listerOutils(): array
    {
        $outils = $this->outilRepository->listerOutils();
        return array_map(fn($outil) => new OutilDTO($outil), $outils);
    }

    public function obtenirOutilParId(string $id): ?OutilDTO
    {
        $outil = $this->outilRepository->OutilParId($id);
        return $outil ? new OutilDTO($outil) : null;
    }

    public function obtenirOutilsParCategorie(string $categorieName): array
    {
        $outils = $this->outilRepository->OutilParCategorie($categorieName);
        return array_map(fn($outil) => new OutilDTO($outil), $outils);
    }
}