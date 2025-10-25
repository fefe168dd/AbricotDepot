<?php
namespace abricotdepot\web\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use abricotdepot\core\application\ports\spi\repositoryInterface\PanierRepository;
use abricotdepot\api\application\entities\User;

class AddToPanierAction
{
    private PanierRepository $panierRepository;

    public function __construct(PanierRepository $panierRepository)
    {
        $this->panierRepository = $panierRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {

        // Récupérer l'ID du panier depuis la session
        $userId = $_COOKIE['user']['id'] ?? null;
        
        // Récupérer les données POST
        $data = $request->getParsedBody();
        $outilId = $data['outil_id'] ?? null;
        $quantite = (int)($data['quantite']);

        if (!$outilId) {
            $response->getBody()->write('Erreur : ID de l\'outil manquant.');
            return $response->withStatus(400);
        }

        
        // Ajouter l'item au panier
        try {
            
            $this->panierRepository->savePanier();
        } catch (\Exception $e) {
            $response->getBody()->write('Erreur lors de l\'ajout au panier : ' . $e->getMessage());
            return $response->withStatus(500);
        }

        // Redirection vers le panier après ajout
        return $response
            ->withHeader('Location', '/panier')
            ->withStatus(302);
    }
}
