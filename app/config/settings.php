<?php

use abricotdepot\api\actions\GetOutilAction;
use abricotdepot\api\actions\GetOutilbyidAction;
use abricotdepot\api\actions\GetReservationAction;
use abricotdepot\api\actions\GetReservationByIDAction;
use abricotdepot\api\actions\GetStockAction;
use abricotdepot\api\actions\GetStockByIdAction;
use abricotdepot\api\actions\GetStockByIdOutilAction;
use abricotdepot\api\actions\GetOutilByCategorie;
use abricotdepot\core\application\usecases\ServiceOutil;
use abricotdepot\core\application\usecases\ServiceReservation;
use abricotdepot\core\application\usecases\ServiceStock;
use abricotdepot\infra\repository\PDOStockRepository;
use abricotdepot\core\application\ports\spi\repositoryInterface\StockRepository;
use abricotdepot\infra\repository\PDOReservationRepository;
use abricotdepot\core\application\ports\spi\repositoryInterface\ReservationRepository;
use abricotdepot\infra\repository\PDOOutilRepository;
use abricotdepot\core\application\ports\spi\repositoryInterface\OutilRepository;

use Psr\Container\ContainerInterface;

return [

    'displayErrorDetails' => true,
    'logs.dir' => __DIR__ . '/../../var/logs',
     'env.config' => __DIR__ . '/.env.dist',

     GetOutilAction::class => function (ContainerInterface $container) {
         return new GetOutilAction($container->get(ServiceOutil::class));
     },
     GetOutilbyidAction::class => function (ContainerInterface $container) {
         return new GetOutilbyidAction($container->get(ServiceOutil::class));
     },
     GetOutilByCategorie::class => function (ContainerInterface $container) {
         return new GetOutilByCategorie($container->get(ServiceOutil::class));
     },
     GetReservationAction::class => function (ContainerInterface $container) {
         return new GetReservationAction($container->get(ServiceReservation::class));
     },
     GetReservationByIDAction::class => function (ContainerInterface $container) {
         return new GetReservationByIDAction($container->get(ServiceReservation::class));
     },
     GetStockAction::class => function (ContainerInterface $container) {
         return new GetStockAction($container->get(ServiceStock::class));
     },
     GetStockByIdAction::class => function (ContainerInterface $container) {
         return new GetStockByIdAction($container->get(ServiceStock::class));
     },
     GetStockByIdOutilAction::class => function (ContainerInterface $container) {
         return new GetStockByIdOutilAction($container->get(ServiceStock::class));
     },

     //infra 
     'outil.pdo' => function (ContainerInterface $container) {
        $config = parse_ini_file($container->get('env.config'));
        $dsn = "{$config['outil.driver']}:host={$config['outil.host']};dbname={$config['outil.database']}";
        $user = $config['outil.username'];
        $password = $config['outil.password'];
        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
     },
     'reservation.pdo' => function (ContainerInterface $container) {
        $config = parse_ini_file($container->get('env.config'));
        $dsn = "{$config['reservation.driver']}:host={$config['reservation.host']};dbname={$config['reservation.database']}";
        $user = $config['reservation.username'];
        $password = $config['reservation.password'];
        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
     },
     'stock.pdo' => function (ContainerInterface $container) {
        $config = parse_ini_file($container->get('env.config'));
        $dsn = "{$config['stock.driver']}:host={$config['stock.host']};dbname={$config['stock.database']}";
        $user = $config['stock.username'];
        $password = $config['stock.password'];
        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
     },

    // Bind StockRepository interface to PDOStockRepository
    StockRepository::class => function (ContainerInterface $container) {
        return new PDOStockRepository($container->get('stock.pdo'));
    },
    ReservationRepository::class => function (ContainerInterface $container) {
        return new PDOReservationRepository($container->get('reservation.pdo'));
    },
    OutilRepository::class => function (ContainerInterface $container) {
        return new PDOOutilRepository($container->get('outil.pdo'));
    },



];