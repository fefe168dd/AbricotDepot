<?php
namespace abricotdepot\web\actions ;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class InscriptionAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        $file = __DIR__ . '/../../../public/html/accueil.html';

        $queryParams = $request->getQueryParams();
        $htmlErreur = '';

        if (isset($queryParams['erreur'])) {
            switch ($queryParams['erreur']) {
                case 1:
                    $htmlErreur = '<div class="ErreurAuth"><p>Impossible de créer le compte, données invalides</p></div>';
                    break;
                case 2:
                    $htmlErreur = '<div class="ErreurAuth"><p>Veuillez remplir tous les champs</p></div>';
                    break;
                case 3 :
                    $htmlErreur = '<div class="ErreurAuth"><p>Le mots de passe doit faire 8 caractères</p></div>' ;
            }
        }



        if (!file_exists($file)) {
            $response->getBody()->write('Erreur : fichier HTML introuvable.');
            return $response->withStatus(500);
        }

        $InscriptionHtml = '
    



<div class="inscrire">
<h1>Inscription</h1>
  <div class="retour"><a href="/connexion">Retour</a></div>  
    <form class="forminscrip" method="post" action="/inscription">
        <h3>Pas de compte? Inscrivez-vous !</h3>
        <div>Pseudo : <input type="text" name="username" required></div>
        <div>Adresse mail : <input type="email" name="email" required></div>
        <div>Mot de passe : <input type="password" name="password" required></div>
        <div>Confirmation mot de passe : <input type="password" name="confirm_password" required></div>
        <div><button type="submit">S\'inscrire</button></div>
        <div class="erreur"><p>'.$htmlErreur.'</p></div>
    </form>
</div>' ;

        $html = file_get_contents($file);


        $html = str_replace('{{Liste Outil}}', $InscriptionHtml, $html);

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }
}
