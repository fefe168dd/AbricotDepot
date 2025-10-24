<?php

namespace abricotdepot\api\actions;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use abricotdepot\core\application\usecases\ServiceReservation;

class GetReservationAction
{
    private ServiceReservation $serviceReservation;

    public function __construct(ServiceReservation $serviceReservation)
    {
        $this->serviceReservation = $serviceReservation;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $reservations = $this->serviceReservation->listerReservations();

        if (empty($reservations)) {
            $response->getBody()->write(json_encode(['error' => 'No reservations found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $reservationsData = array_map(fn($reservation) => $reservation, $reservations);
        $response->getBody()->write(json_encode($reservationsData));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
