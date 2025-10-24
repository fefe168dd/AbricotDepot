<?php
namespace abricotdepot\web\actions ;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PostConnexionAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        $file = __DIR__ . '/../../../public/html/accueil.html';


        if (!file_exists($file)) {
            $response->getBody()->write('Erreur : fichier HTML introuvable.');
            return $response->withStatus(500);
        }

        $html = file_get_contents($file);


        $apiUrl = 'http://apicot:80/auth/signin' ;

        $parsedBody = $request->getParsedBody();
        $email = $parsedBody['identifiant'] ?? null;
        $password = $parsedBody['password'] ?? null;

        if (!$email || !$password) {
            $response->getBody()->write('Champs manquants.');
            return $response->withStatus(400);
        }

        $data = json_encode([
            'email' => $email,
            'password' => $password
        ]);

        $ch = curl_init($apiUrl);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);

        $res = curl_exec($ch);

        $res = json_decode($res , true) ;

// Fermeture de la session cURL
        curl_close($ch);

        $authOk = $res['success'];
        $token = $res['data'] ?? null;


        if ($authOk) {

                setcookie('access_token', $token['accessToken']);
                setcookie('refresh_token', $token['refreshToken']);
                setcookie('user_id', $token['user']['id']);


                return $response
                    ->withHeader('Location', '/')
                    ->withStatus(302);
            } else {
                // Sinon, redirige vers la page de connexion avec un message d’erreur
                return $response
                    ->withHeader('Location', '/connexion?erreur=1')
                    ->withStatus(302);
            }
        }
    }
