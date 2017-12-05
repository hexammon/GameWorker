<?php

require_once __DIR__ . '/../vendor/autoload.php';

$game = (new \Hexammon\GameWorker\GameBuilder())->build($players, $boardConfig, $ruleSet);
$application = new \Hexammon\GameWorker\Application($game);
$gameUUID = \Lootils\Uuid\Uuid::createV4();
$client = new Thruway\Peer\Client('hexammon');

$router = new \Hexammon\GameWorker\Router($application, $gameUUID);

$client->on('open', function (\Thruway\ClientSession $session) use ($router) {
    $router->bindActions($session);
});

try {
    $client->start();
} catch (Exception $e) {
    echo 'Cannot start thruway client. ';
    exit(1);
}