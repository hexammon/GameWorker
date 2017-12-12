<?php

use Thruway\Message\AuthenticateMessage;

require_once __DIR__ . '/../vendor/autoload.php';
$client = new Thruway\Peer\Client('hexammon', $loop);
$client->setAuthId('admin');
$client->setAuthMethods(['jwt']);
$jwt = (new \FreeElephants\Jwt\Firebase\FirebaseEncoderAdapter('foo'))->encode([
    'authid' => 'admin',
    'authroles' => ['admin']
], 'HS256');
$client->addClientAuthenticator(new class($jwt, 'admin') implements \Thruway\Authentication\ClientAuthenticationInterface
{

    private $authId;
    /**
     * @var string
     */
    private $jwt;

    public function __construct(string $jwt, $authid)
    {

        $this->jwt = $jwt;
        $this->authId = $authid;
    }

    /**
     * Get AuthID
     *
     * @return mixed
     */
    public function getAuthId()
    {
        return $this->authId;
    }

    /**
     * Set AuthID
     *
     * @param mixed $authid
     */
    public function setAuthId($authid)
    {
        $this->authId = $authid;
    }

    /**
     * Get list supported authentication method
     *
     * @return array
     */
    public function getAuthMethods()
    {
        return ['jwt'];
    }

    /**
     * Get authentication message from challenge message
     *
     * @param \Thruway\Message\ChallengeMessage $msg
     * @return \Thruway\Message\AuthenticateMessage|boolean
     */
    public function getAuthenticateFromChallenge(\Thruway\Message\ChallengeMessage $msg)
    {
        return new AuthenticateMessage($this->jwt);
    }
});
$client->on('open', function (\Thruway\ClientSession $session) use ($loop) {

    // TODO set auth rols from game dispatcher

    $session->call('flush_authorization_rules', [true]);

    $initGameRule = new stdClass();
    $initGameRule->role = 'init_game';
    $initGameRule->action = 'register';
    $initGameRule->uri = 'net.hexammon.games.create';
    $initGameRule->allow = true;
    $session->call('add_authorization_rule', [$initGameRule]);

    $session->register('net.hexammon.games.create', function ($playesIds, $boardParams) use ($loop) {
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
