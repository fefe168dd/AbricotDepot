<?php
namespace abricotdepot\web\actions;

use abricotdepot\core\application\ports\spi\repositoryInterface\PanierRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RemovePanierAction
{
    private PanierRepository $panierRepository;

    public function __construct(PanierRepository $panierRepository)
    {
        $this->panierRepository = $panierRepository;
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

        // Retire 1 exemplaire du panier
        $this->panierRepository->removeItem($userId, $outilId, $datedebut, $datefin);

        return $response->withHeader('Location', '/panier')->withStatus(302);
    }
}
