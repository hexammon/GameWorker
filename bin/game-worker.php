<?php

require_once __DIR__ . '/../vendor/autoload.php';

// params getting
$players = [];
foreach (json_decode($argv[1]) as $playerId) {
    $players[$playerId] = new \Hexammon\GameWorker\Player();
}

$boardParams = json_decode($argv[2]);
$boardConfig = new \Hexammon\GameWorker\BoardConfig(...$boardParams);
$ruleSet = new \Hexammon\HexoNards\Game\Rules\ClassicRuleSet();

// Application logic
$game = (new \Hexammon\GameWorker\GameBuilder())->build($players, $boardConfig, $ruleSet);
$application = new \Hexammon\GameWorker\Application($game);
$gameUUID = \Lootils\Uuid\Uuid::createV4();
$client = new Thruway\Peer\Client('hexammon');

$router = new \Hexammon\GameWorker\Router($application, $gameUUID);

$client->on('open', function (\Thruway\ClientSession $session) use ($router) {
    $router->bindActions($session);

});


// Transport layer
try {
    $client->addTransportProvider(new \Thruway\Transport\PawlTransportProvider());
    $client->start();
} catch (Exception $e) {
    echo 'Cannot start thruway client. '. PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString();
    exit(1);
}