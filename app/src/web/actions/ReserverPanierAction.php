<?php
namespace abricotdepot\web\actions;

use abricotdepot\core\application\ports\spi\repositoryInterface\PanierRepository;
use abricotdepot\core\application\ports\spi\repositoryInterface\ReservationRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReserverPanierAction
{
    private PanierRepository $panierRepository;
    private ReservationRepository $reservationRepository;

    public function __construct(PanierRepository $panierRepository, ReservationRepository $reservationRepository)
    {
        $this->panierRepository = $panierRepository;
        $this->reservationRepository = $reservationRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $userId = $_COOKIE['user_id'] ?? null;
        if (!$userId) {
            $response->getBody()->write('Vous devez être connecté pour réserver.');
            return $response->withStatus(403);
        }

        // Récupérer les items du panier
        $panier = $this->panierRepository->getPanierItemsByUserId($userId);

        if (empty($panier['items'])) {
            $response->getBody()->write('Votre panier est vide.');
            return $response->withStatus(400);
        }

        // Pour chaque item → créer une réservation
        foreach ($panier['items'] as $item) {
            $this->reservationRepository->createReservation(
                $userId,
                $item['outil_id'],
                $item['datedebut'],
                $item['datefin'],
                $item['quantity']
            );
        }

        // Vider le panier
        $this->panierRepository->clearPanier($userId);

        // Rediriger vers une page de confirmation
        return $response
            ->withHeader('Location', '/reservation/confirmation')
            ->withStatus(302);
    }
}
