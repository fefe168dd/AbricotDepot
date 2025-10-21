<?php 
namespace abricotdepot\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use abricotdepot\core\application\ports\spi\repositoryInterface\OutilRepository;
use abricotdepot\core\application\ports\api\dto\OutilDTO;

class GetOutilAction 
{
    private OutilRepository $outilRepository;

    public function __construct(OutilRepository $outilRepository)
    {
        $this->outilRepository = $outilRepository;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $outils = $this->outilRepository->listerOutils();

        if (empty($outils)) {
            $response->getBody()->write(json_encode(['error' => 'No outils found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $outilsData = array_map(fn($outil) => new OutilDTO($outil), $outils);
        $response->getBody()->write(json_encode($outilsData));
        return $response->withHeader('Content-Type', 'application/json');
    }
}