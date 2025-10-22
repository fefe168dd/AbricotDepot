<?php 
namespace abricotdepot\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use abricotdepot\core\application\usecases\ServiceStock;

class GetStockByIDOutilAction
{
    private ServiceStock $serviceStock;

    public function __construct(ServiceStock $serviceStock)
    {
        $this->serviceStock = $serviceStock;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $idOutil = $args['id'];
        $stock = $this->serviceStock->obtenirStockParOutilId($idOutil);

        if (!$stock) {
            $response->getBody()->write(json_encode(['error' => 'Stock not found for the given Outil ID']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $stockData = $stock;
        $response->getBody()->write(json_encode($stockData));
        return $response->withHeader('Content-Type', 'application/json');
    }
}