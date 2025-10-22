<?php
namespace abricotdepot\api\actions;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use abricotdepot\core\application\usecases\ServiceReservation;

class GetReservationByOutilAndDatesAction 
{
    private ServiceReservation $serviceReservation;

    public function __construct(ServiceReservation $serviceReservation)
    {
        $this->serviceReservation = $serviceReservation;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $dateDebut = new \DateTime($request->getQueryParams()['date_debut']);
        $dateFin = new \DateTime($request->getQueryParams()['date_fin']);
        $reservation = $this->serviceReservation->obtenirReservationParOutilIdEtDate($id, $dateDebut, $dateFin);

        if (!$reservation) {
            $response->getBody()->write(json_encode(['error' => 'Reservation not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $reservationData = $reservation;
        $response->getBody()->write(json_encode($reservationData));
        return $response->withHeader('Content-Type', 'application/json');
    }
}