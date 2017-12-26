<?php

require __DIR__ . '/../vendor/autoload.php';

$encoder = new \FreeElephants\Jwt\Firebase\FirebaseEncoderAdapter('hexammon-secret-jwt-key');

$generateJwt = function (string $authId, array $authRoles) use ($encoder) {
    $jwt = $encoder->encode([
        'authid' => $authId,
        'authroles' => $authRoles,
    ], 'HS256');

    echo $authId . PHP_EOL;
    echo $jwt . PHP_EOL;
};

$generateJwt('game-dispatcher', ['game_dispatcher']);

$generateJwt('player', ['player']);

$generateJwt('game-watcher', ['game_watcher']);
