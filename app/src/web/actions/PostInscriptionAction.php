<?php
namespace abricotdepot\web\actions ;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PostInscriptionAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        $file = __DIR__ . '/../../../public/html/accueil.html';


        if (!file_exists($file)) {
            $response->getBody()->write('Erreur : fichier HTML introuvable.');
            return $response->withStatus(500);
        }

        $html = file_get_contents($file);


        $apiUrl = 'http://apicot:80/auth/signup' ;

        $parsedBody = $request->getParsedBody();
        $username = $parsedBody['username'] ?? null;
        $password = $parsedBody['password'] ?? null;
        $confPassword = $parsedBody['confirm_password'] ?? null;
        $email = $parsedBody['email'] ?? null;

        if (!$email || !$password ||!$username || !$confPassword) {
            return $response
                ->withHeader('Location', '/connexion?erreur=2')
                ->withStatus(302);
        }

        if (strlen($password)<8) {
            return $response
                ->withHeader('Location', '/inscription?erreur=3')
                ->withStatus(302);
        }

        $data = json_encode([
            'email' => $email,
            'username' => $username ,
            'password' => $password ,
            'password_confirm' => $confPassword
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


        if ($authOk) {

            return $response
                ->withHeader('Location', '/connexion')
                ->withStatus(302);
        } else {
            // Sinon, redirige vers la page de connexion avec un message d’erreur
            return $response
                ->withHeader('Location', '/inscription?erreur=1')
                ->withStatus(302);
        }

    }
}