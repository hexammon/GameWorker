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

    $createGameRule = new \stdClass();
    $createGameRule->role = 'player';
    $createGameRule->action = 'register';
    $createGameRule->uri = 'net.hexammon.game.create';
    $createGameRule->allow = true;
    $session->call('add_authorization_rule', [$createGameRule]);

//    $createGameRule = new \stdClass();
//    $createGameRule->role = 'player';
//    $createGameRule->action = 'register';
//    $createGameRule->uri = 'net.hexammon.game.create';
//    $createGameRule->allow = true;
//    $session->call('add_authorization_rule', [$createGameRule]);
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
