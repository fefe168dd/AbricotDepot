<?php
namespace abricotdepot\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Ramsey\Uuid\Uuid;
use abricotdepot\core\application\usecases\ServicePanier;
use abricotdepot\core\domain\entities\Panier\Panier;

class AddToPanierAction
{
    private ServicePanier $servicePanier;

    public function __construct(ServicePanier $servicePanier)
    {
        $this->servicePanier = $servicePanier;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // Récupérer les infos du token via le middleware
        $userId = $request->getAttribute('userId');
        if (!$userId) {
            return $this->jsonResponse($response, ['error' => 'Utilisateur non authentifié'], 401);
        }

        // Données JSON envoyées par l’action web
        $data = json_decode($request->getBody()->getContents(), true);
        $outilId = $data['outil_id'] ?? null;
        $quantite = (int)($data['quantite'] ?? 0);
        $dateDebut = new \DateTime($data['date_debut'] ?? 'now');
        $dateFin = new \DateTime($data['date_fin'] ?? 'now');

        if (!$outilId || $quantite <= 0) {
            return $this->jsonResponse($response, ['error' => 'Données invalides'], 400);
        }

        // Création d’un panier avec UUID unique
        $panier = new Panier(
            Uuid::uuid4()->toString(),
            $userId,
            $outilId,
            $quantite,
            $dateDebut,
            $dateFin
        );

        try {
            $this->servicePanier->savePanier($panier);
            return $this->jsonResponse($response, ['success' => true, 'message' => 'Ajout réussi'], 201);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'error' => 'Erreur lors de l’ajout via l\'api : ' . $e->getMessage()
            ], 500);
        }
    }

    private function jsonResponse(Response $response, array $data, int $status): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }

}
