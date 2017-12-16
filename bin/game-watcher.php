<?php

require_once __DIR__ . '/../vendor/autoload.php';

$client = new Thruway\Peer\Client('hexammon', $loop);
$client->setAuthId('game-watcher');
$client->setAuthMethods(['jwt']);
$jwt = (new \FreeElephants\Jwt\Firebase\FirebaseEncoderAdapter('foo'))->encode([
    'authid' => 'game-watcher',
    'authroles' => ['game-watcher']
], 'HS256');

$client->addClientAuthenticator(new \Hexammon\Wamp\ClientJwtAuthenticator($jwt, 'game-watcher'));

$client->on('open', function (\Thruway\ClientSession $session) use ($loop) {

    $session->register('net.hexammon.game.create', function ($playesIds, $boardParams) use ($loop) {
        $newGameWorkerCmd = sprintf('php /srv/game-worker/game-worker.php %s %s', json_encode($playesIds),
            json_encode($boardParams));
        $process = new React\ChildProcess\Process($newGameWorkerCmd);
        try {
            $process->start($loop);
        } catch (Throwable $e) {
            var_dump($e);
        }

        $process->stdout->on('data', function ($chunk) {
            echo $chunk;
        });

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
