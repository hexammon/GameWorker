<?php

use FreeElephants\Jwt\Firebase\FirebaseEncoderAdapter;
use Hexammon\Wamp\ClientJwtAuthenticator;
use React\ChildProcess\Process;
use React\EventLoop\Factory;
use Thruway\ClientSession;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;

require_once __DIR__ . '/../vendor/autoload.php';

define('JWT_SECRET_KEY', getenv('JWT_SECRET_KEY'));
$loop = Factory::create();
$client = new Client('hexammon', $loop);
$client->setAuthId('game-watcher');
$client->setAuthMethods(['jwt']);
$jwt = (new FirebaseEncoderAdapter(JWT_SECRET_KEY))->encode([
    'authid' => 'game-watcher',
    'authroles' => ['game_watcher']
], 'HS256');

$client->addClientAuthenticator(new ClientJwtAuthenticator($jwt, 'game-watcher'));

$client->on('open', function (ClientSession $session) use ($loop) {

    $session->register('net.hexammon.game.create', function ($args) use ($loop) {

        list($playesIds, $boardType, $numberOfRows, $numberOfCols) = $args;
        $playersCmdArg = json_encode($playesIds);
        $boardCmdArg = json_encode([$boardType, $numberOfRows, $numberOfCols]);
        $newGameWorkerCmd = sprintf('php /srv/game-worker/bin/game-worker.php \'%s\' \'%s\'', $playersCmdArg, $boardCmdArg);
        $process = new Process($newGameWorkerCmd);
        try {
            $process->start($loop);
        } catch (Throwable $e) {
            var_dump($e);
        }

        $process->stdout->on('data', function ($chunk) {
            echo $chunk;
        });
        return $args;

        $process->on('exit', function ($exitCode, $termSignal) {
            echo 'Process exited with code ' . $exitCode . PHP_EOL;
        });
        echo __METHOD__;
    });
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
