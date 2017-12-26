<?php

use FreeElephants\Jwt\Firebase\FirebaseEncoderAdapter;
use Hexammon\GameWorker\Application;
use Hexammon\GameWorker\BoardConfig;
use Hexammon\GameWorker\GameBuilder;
use Hexammon\GameWorker\Player;
use Hexammon\GameWorker\Router;
use Hexammon\HexoNards\Game\Rules\ClassicRuleSet;
use Hexammon\Wamp\ClientJwtAuthenticator;
use Lootils\Uuid\Uuid;
use React\EventLoop\Factory;
use Thruway\ClientSession;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;

require_once __DIR__ . '/../vendor/autoload.php';

// params getting
$players = [];

foreach (json_decode($argv[1]) as $playerId) {
    $players[$playerId] = new Player($playerId);
}

$boardParams = json_decode($argv[2]);
$boardConfig = new BoardConfig(...$boardParams);
$ruleSet = new ClassicRuleSet();

// Application logic
$game = (new GameBuilder())->build($players, $boardConfig, $ruleSet);
$application = new Application($game);

$gameUUID = Uuid::createV4();
$workerAuthId = 'game-worker' . $gameUUID;

define('JWT_SECRET_KEY', getenv('JWT_SECRET_KEY'));
$loop = Factory::create();
$client = new Client('hexammon', $loop);
$client->setAuthId($workerAuthId);
$client->setAuthMethods(['jwt']);
$jwt = (new FirebaseEncoderAdapter(JWT_SECRET_KEY))->encode([
    'authid' => $workerAuthId,
    'authroles' => ['game-worker']
], 'HS256');

$client->addClientAuthenticator(new ClientJwtAuthenticator($jwt, 'game-watcher'));

$router = new Router($application, $gameUUID);

$client->on('open', function (ClientSession $session) use ($router) {
    $router->bindActions($session);

});


// Transport layer
try {
    $url = sprintf('ws://%s:9000/', gethostbyname('wamp-router'));
    $client->addTransportProvider(new PawlTransportProvider($url));
    $client->start();
} catch (Exception $e) {
    echo 'Cannot start thruway client. ' . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString();
    exit(1);
}