<?php

namespace Hexammon\GameWorkerTest;

use Hexammon\GameWorker\Application;
use Hexammon\HexoNards\Game\Game;
use PHPUnit\Framework\TestCase;

class StartUpTest extends TestCase
{

    public function testGameExists()
    {
        $game = $this->createMock(Game::class);
        /**@var Game $game */
        $app = new Application($game);
        $this->assertInstanceOf(Game::class, $app->getGame());
    }

}