<?php 

namespace abricotdepot\api\actions;

use abricotdepot\core\application\ports\api\dto\RsvinputDTO;
use abricotdepot\core\application\usecases\ServiceReservation;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
class AddReservationAction 
{
    private ServiceReservation $serviceReservation;

    public function __construct(ServiceReservation $serviceReservation)
    {
        $this->serviceReservation = $serviceReservation;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $requiredFields = [ 'outil_id', 'quantity', 'dateDebut', 'dateFin'];
        $missingFields = array_filter($requiredFields, fn($field) => empty($data[$field]));
        if (!empty($missingFields)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Missing required fields: ' . implode(', ', $missingFields)
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        try {
            $reservationDTO = new RsvinputDTO(
                (string)$data['outil_id'],
                (int)$data['quantity'],
                new \DateTime($data['dateDebut']),
                new \DateTime($data['dateFin'])
            );
            $this->serviceReservation->sauvegarderReservation($reservationDTO);
            $response->getBody()->write(json_encode(['success' => true]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }
}