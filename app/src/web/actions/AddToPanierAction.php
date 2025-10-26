<?php
namespace abricotdepot\web\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AddToPanierAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // Récupération des données envoyées par le formulaire
        $data = $request->getParsedBody();
        $outilId   = $args['id'] ?? null;
        $quantite  = $data['quantite'] ?? null;
        $dateDebut = $data['date_debut'] ?? null;
        $dateFin   = $data['date_fin'] ?? null;

        // Vérification que tous les champs obligatoires sont présents
        if (!$outilId || !$quantite || !$dateDebut || !$dateFin) {
            $response->getBody()->write('Champs manquants.');
            return $response->withStatus(400);
        }

        // Récupération du token d'accès depuis les cookies
        $token = $_COOKIE['access_token'] ?? null;
        if (!$token) {
            // Si l'utilisateur n'est pas connecté, redirection vers la page de connexion
            return $response->withHeader('Location', '/connexion')->withStatus(302);
        }

        // Préparation des données JSON à envoyer à l'API
        // On n'envoie pas l'user_id : l'API le récupère depuis le token
        $apiUrl = 'http://apicot:80/panier/ajoutProduit';
        $payload = json_encode([
            'outil_id'   => $outilId,
            'quantite'   => $quantite,
            'date_debut' => $dateDebut,
            'date_fin'   => $dateFin,
        ]);

        // Initialisation de cURL pour appeler l'API
        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,         
            ],
            CURLOPT_POSTFIELDS     => $payload,
        ]);

        // Exécution de la requête et récupération du code HTTP
        $res = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Vérification si l'appel API a échoué
        if ($statusCode < 200 || $statusCode >= 300) {
            $response->getBody()->write("<pre>
Erreur lors de l'ajout au panier
Code HTTP : $statusCode
Réponse API : $res
Erreur cURL : $curlError
</pre>");
            return $response->withStatus(500);
        }
        // Si tout s'est bien passé, redirection vers le panier
        return $response->withHeader('Location', '/panier')->withStatus(302);
    }
}
