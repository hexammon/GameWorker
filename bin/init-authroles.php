<?php

require __DIR__ . '/../vendor/autoload.php';

$client = new Thruway\Peer\Client('hexammon');
$client->setAuthId('admin');
$client->setAuthMethods(['jwt']);
$jwt = (new \FreeElephants\Jwt\Firebase\FirebaseEncoderAdapter('hexammon-secret-jwt-key'))->encode([
    'authid' => 'admin',
    'authroles' => ['admin']
], 'HS256');
$client->addClientAuthenticator(new \Hexammon\Wamp\ClientJwtAuthenticator($jwt, 'admin'));
$client->on('open', function (\Thruway\ClientSession $session) {

    $session->call('flush_authorization_rules', [true]);

    $createGameCallerRule = new \stdClass();
    $createGameCallerRule->role = 'player';
    $createGameCallerRule->action = 'call';
    $createGameCallerRule->uri = 'net.hexammon.game.create';
    $createGameCallerRule->allow = true;
    $session->call('add_authorization_rule', [$createGameCallerRule]);

    $createGameCalleRule = new \stdClass();
    $createGameCalleRule->role = 'game_watcher';
    $createGameCalleRule->action = 'register';
    $createGameCalleRule->uri = 'net.hexammon.game.create';
    $createGameCalleRule->allow = true;
    $session->call('add_authorization_rule', [$createGameCalleRule]);
//
    $gameWorkerCalleRule = new \stdClass();
    $gameWorkerCalleRule->role = 'game_worker';
    $gameWorkerCalleRule->action = 'register';
    $gameWorkerCalleRule->uri = 'net.hexammon.game.';
    $gameWorkerCalleRule->allow = true;
    $session->call('add_authorization_rule', [$gameWorkerCalleRule]);
});

// Transport layer
try {
//    $url = sprintf('ws://%s:9000/', gethostbyname('wamp-router'));
    $url = sprintf('ws://%s:9000/', '127.0.0.1');
    $client->addTransportProvider(new \Thruway\Transport\PawlTransportProvider($url));
    $client->start();
} catch (Exception $e) {
    echo 'Cannot start thruway client. ' . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString();
    exit(1);
}
