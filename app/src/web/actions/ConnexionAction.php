<?php
namespace abricotdepot\web\actions ;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ConnexionAction
{
    public function __invoke(Request $request, Response $response , array $args): Response
    {
        $file = __DIR__ . '/../../../public/html/accueil.html';

        $htmlErreur = '' ;

        $queryParams = $request->getQueryParams();
        if (isset($queryParams['erreur']) && $queryParams['erreur'] == 1) {
            $htmlErreur = '<div class="ErreurAuth"><p>Identifiant ou mot de passe incorrect</p></div>';
        }


        if (!file_exists($file)) {
            $response->getBody()->write('Erreur : fichier HTML introuvable.');
            return $response->withStatus(500);
        }

        $html = file_get_contents($file);

        $connexionHTML = '<div>
        <h1>Connectez-vous</h1>
    </div>
    <div class="formulaire">
    <form class="formconnexion" method="post" action="/connexion">
        <div class="identifiant">Adresse mail : <input type="email" name="identifiant" required></div>
        <div class="password">Mot de passe : <input type="password" name="password" required></div>
        <div class="submit"><button type="submit">Se Connecter</button></div>
        <div class="inscription"><a href="/inscription">Pas de compte ? Inscrivez-vous !</a></div>
        '. $htmlErreur .'
    </form>
    </div>' ;


        $html = str_replace('{{Liste Outil}}', $connexionHTML, $html);
        $menu = (new GenerateMenuClasse())->generateMenu();
        $html = str_replace('{{Menu}}', $menu, $html);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }
}