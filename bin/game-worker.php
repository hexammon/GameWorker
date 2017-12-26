<?php

require_once __DIR__ . '/../vendor/autoload.php';

// params getting
$players = [];

foreach (json_decode($argv[1]) as $playerId) {
    $players[$playerId] = new \Hexammon\GameWorker\Player();
}

//var_dump($players);
$boardParams = json_decode($argv[2]);
//var_dump($argv[2]);
//var_dump($boardParams);
$boardConfig = new \Hexammon\GameWorker\BoardConfig(...$boardParams);
//var_dump($boardConfig);
$ruleSet = new \Hexammon\HexoNards\Game\Rules\ClassicRuleSet();

// Application logic
$game = (new \Hexammon\GameWorker\GameBuilder())->build($players, $boardConfig, $ruleSet);
$application = new \Hexammon\GameWorker\Application($game);

$gameUUID = \Lootils\Uuid\Uuid::createV4();
$workerAuthId = 'game-worker' . $gameUUID;

define('JWT_SECRET_KEY', getenv('JWT_SECRET_KEY'));
$loop = \React\EventLoop\Factory::create();
$client = new Thruway\Peer\Client('hexammon', $loop);
$client->setAuthId($workerAuthId);
$client->setAuthMethods(['jwt']);
$jwt = (new \FreeElephants\Jwt\Firebase\FirebaseEncoderAdapter(JWT_SECRET_KEY))->encode([
    'authid' => $workerAuthId,
    'authroles' => ['game-worker']
], 'HS256');

$client->addClientAuthenticator(new \Hexammon\Wamp\ClientJwtAuthenticator($jwt, 'game-watcher'));

$router = new \Hexammon\GameWorker\Router($application, $gameUUID);

$client->on('open', function (\Thruway\ClientSession $session) use ($router) {
    $router->bindActions($session);

});


// Transport layer
try {
    $url = sprintf('ws://%s:9000/', gethostbyname('wamp-router'));
    $client->addTransportProvider(new \Thruway\Transport\PawlTransportProvider($url));
    $client->start();
} catch (Exception $e) {
    echo 'Cannot start thruway client. ' . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString();
    exit(1);
}