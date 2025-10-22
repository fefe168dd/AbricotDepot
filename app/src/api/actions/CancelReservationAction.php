<?php 

namespace abricotdepot\api\actions;

use abricotdepot\core\application\usecases\ServiceReservation;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CancelReservationAction 
{
    private ServiceReservation $serviceReservation;

    public function __construct(ServiceReservation $serviceReservation)
    {
        $this->serviceReservation = $serviceReservation;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $reservationId = $args['id'] ?? null;

        if (!$reservationId) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'ID de réservation manquant'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $this->serviceReservation->annulerReservation($reservationId);
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Réservation annulée avec succès'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }
}
