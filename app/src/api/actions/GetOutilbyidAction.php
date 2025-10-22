<?php 
namespace abricotdepot\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use abricotdepot\core\application\ports\api\dto\OutilDTO;
use abricotdepot\core\application\ports\spi\repositoryInterface\OutilRepository;
use abricotdepot\core\application\usecases\ServiceOutil;

class GetOutilbyidAction
{
    private ServiceOutil $outilService;

    public function __construct(ServiceOutil $outilService)
    {
        $this->outilService = $outilService;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $outil = $this->outilService->obtenirOutilParId($id);

        if (!$outil) {
            $response->getBody()->write(json_encode(['error' => 'Outil not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $outilData = $outil;
        $response->getBody()->write(json_encode($outilData));
        return $response->withHeader('Content-Type', 'application/json');
    }
}