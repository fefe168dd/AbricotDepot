<?php
namespace abricotdepot\web\actions;

use abricotdepot\core\application\ports\spi\repositoryInterface\PanierRepository;
use abricotdepot\core\application\ports\spi\repositoryInterface\StockRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AddPanierAction
{
    private PanierRepository $panierRepository;
    private StockRepository $stockRepository;

    public function __construct(PanierRepository $panierRepository, StockRepository $stockRepository)
    {
        $this->panierRepository = $panierRepository;
        $this->stockRepository = $stockRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $cookies = $request->getCookieParams();
        $userId = $cookies['user_id'] ?? null;
        $outilId = $args['outil_id'] ?? null;
        $datedebut = $args['datedebut'] ?? null;
        $datefin = $args['datefin'] ?? null;

        if (!$userId) {
            return $response->withHeader('Location', '/connexion')->withStatus(302);
        }

        if (!$outilId || !$datedebut || !$datefin) {
            $response->getBody()->write("Outil ou dates manquantes.");
            return $response->withStatus(400);
        }

        // Récupère le stock total
        $stock = $this->stockRepository->StockParOutilId($outilId);
        if (!$stock) {
            $response->getBody()->write("Outil introuvable en stock.");
            return $response->withStatus(404);
        }

        // Vérifie combien d’exemplaires sont déjà dans le panier pour ces dates
        $panierItems = $this->panierRepository->getPanierItemsByUserIdAndOutilId($userId, $outilId);
        $existingQty = 0;
        foreach ($panierItems as $item) {
            if ($item->getDateDebut()->format('Y-m-d H:i:s') === $datedebut
                && $item->getDateFin()->format('Y-m-d H:i:s') === $datefin) {
                $existingQty = $item->getQuantity();
            }
        }

        if ($existingQty >= $stock->getQuantity()) {
            $response->getBody()->write("Stock insuffisant. Vous ne pouvez pas ajouter plus d'exemplaires.");
            return $response->withStatus(400);
        }

        // Ajoute 1 exemplaire au panier
        $this->panierRepository->addItem($userId, $outilId, 1, $datedebut, $datefin);

        return $response->withHeader('Location', '/panier')->withStatus(302);
    }
}

?>