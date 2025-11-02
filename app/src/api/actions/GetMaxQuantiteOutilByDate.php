<?php

namespace abricotdepot\api\actions;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetMaxQuantiteOutilByDate
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $outilId = $args['id'];
        $dateDebut = $args['date_debut'];
        $dateFin = $args['date_fin'];
        $reservationUrl = "http://apicot:80/reservations/{$outilId}/{$dateDebut}/{$dateFin}";
        $stockUrl       = "http://apicot:80/outils/{$outilId}/stocks";


        // --- Récupération des réservations ---
        $ch1 = curl_init($reservationUrl);
        curl_setopt_array($ch1, [
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $resReservations = curl_exec($ch1);
        curl_close($ch1);

        $reservations = json_decode($resReservations, true) ?? [];

        // --- Récupération du stock ---
        $ch2 = curl_init($stockUrl);
        curl_setopt_array($ch2, [
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $resStock = curl_exec($ch2);
        curl_close($ch2);

        $stockData = json_decode($resStock, true) ?? [];

        // --- Calcul total réservé ---
        $totalReserve = 0;
        if (is_array($reservations)) {
            if (array_keys($reservations) === range(0, count($reservations) - 1)) {
                // Tableau de réservations
                foreach ($reservations as $res) {
                    $totalReserve += $res['quantity'] ?? 0;
                }
            } else {
                // Une seule réservation
                $totalReserve = $reservations['quantity'] ?? 0;
            }
        }

        $stockTotal  = $stockData['quantity'] ?? 0;
        $maxQuantite = max($stockTotal - $totalReserve, 0);
        
        // --- Retourne juste le nombre, pas du JSON ---
        $response->getBody()->write((string)$maxQuantite);
        return $maxQuantite;
    }
}
