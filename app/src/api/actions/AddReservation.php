<?php 
namespace abricotdepot\api\actions;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use abricotdepot\core\application\usecases\ServiceReservation;
use  abricotdepot\core\application\ports\api\dto\ReservationDTO;

class AddReservationAction
{
    private ServiceReservation $serviceReservation;

    public function __construct(ServiceReservation $serviceReservation)
    {
        $this->serviceReservation = $serviceReservation;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
       $body = $request->getParsedBody();
       if (!isset($body['user_id'], $body['stock_id'], $body['quantity'], $body['DateDebut'], $body['DateFin'])) {
           $response->getBody()->write(json_encode(['error' => 'Missing required fields']));
           return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
       }

       try {
        $dateDebut = new \DateTime($body['DateDebut']);
        $dateFin = new \DateTime($body['DateFin']);
       }
         catch (\Exception $e) {
          $response->getBody()->write(json_encode(['error' => 'Invalid date format']));
          return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
         }
         $reservation = new ReservationDTO(
              $body['user_id'],
              $body['stock_id'],
              $body['quantity'],
              $dateDebut,
              $dateFin
         );

         try {
             $newReservation = $this->serviceReservation->sauvegarderReservation($reservation);
         } catch (\Throwable $e) {
             $response->getBody()->write(json_encode(['error' => 'Failed to save reservation']));
             return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
         }

         if ($newReservation === null || !is_object($newReservation) || !method_exists($newReservation, 'getId')) {
             $response->getBody()->write(json_encode(['error' => 'Failed to create reservation']));
             return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
         }

        $response->getBody()->write(json_encode(['message' => 'Reservation added successfully', 'reservation_id' => $newReservation->getId()]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');


}

}