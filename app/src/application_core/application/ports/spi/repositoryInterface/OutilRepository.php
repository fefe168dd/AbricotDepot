<?php 
namespace App\core\application\ports\spi\repositoryInterface;

use App\core\domain\entities\Outil\Outil;

interface OutilRepository 
{
    public function listerOutils(): array;
    public function OutilParId(string $id): ?Outil;
    public function OutilParCategorie(string $categorieName): array;
}