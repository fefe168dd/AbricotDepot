<?php
namespace abricotdepot\web\actions;

use abricotdepot\core\application\ports\spi\repositoryInterface\PanierRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PanierAddAction
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

        if (!$userId) {
            return $response->withHeader('Location', '/connexion')->withStatus(302);
        }

        // Ajoute 1 exemplaire de l’outil au panier
        $this->panierRepository->addItem($userId, $outilId, 1);

        return $response->withHeader('Location', '/panier')->withStatus(302);
    }
}
