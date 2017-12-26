<?php

require_once __DIR__ . '/../vendor/autoload.php';

define('JWT_SECRET_KEY', getenv('JWT_SECRET_KEY'));
$loop = \React\EventLoop\Factory::create();
$client = new Thruway\Peer\Client('hexammon', $loop);
$client->setAuthId('game-watcher');
$client->setAuthMethods(['jwt']);
$jwt = (new \FreeElephants\Jwt\Firebase\FirebaseEncoderAdapter(JWT_SECRET_KEY))->encode([
    'authid' => 'game-watcher',
    'authroles' => ['game_watcher']
], 'HS256');

$client->addClientAuthenticator(new \Hexammon\Wamp\ClientJwtAuthenticator($jwt, 'game-watcher'));

$client->on('open', function (\Thruway\ClientSession $session) use ($loop) {

    $session->register('net.hexammon.game.create', function ($args) use ($loop) {

        list($playesIds, $boardType, $numberOfRows, $numberOfCols) = $args;
        $playersCmdArg = json_encode($playesIds);
        $boardCmdArg = json_encode([$boardType, $numberOfRows, $numberOfCols]);
        $newGameWorkerCmd = sprintf('php /srv/game-worker/bin/game-worker.php \'%s\' \'%s\'', $playersCmdArg, $boardCmdArg);
        $process = new React\ChildProcess\Process($newGameWorkerCmd);
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
    $client->addTransportProvider(new \Thruway\Transport\PawlTransportProvider($url));
    $client->start();
} catch (Exception $e) {
    echo 'Cannot start thruway client. ' . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString();
    exit(1);
}
