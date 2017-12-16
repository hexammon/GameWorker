<?php

require __DIR__ . '/../vendor/autoload.php';

$encoder = new \FreeElephants\Jwt\Firebase\FirebaseEncoderAdapter('hexammon-secret-jwt-key');
$jwt = $encoder->encode([
    'authid' => 'player',
    'authroles' => [
        'player',
    ],
], 'HS256');

echo $jwt;
