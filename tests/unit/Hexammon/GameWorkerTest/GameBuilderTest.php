<?php

namespace Hexammon\GameWorkerTest;

use Hexammon\GameWorker\BoardConfig;
use Hexammon\GameWorker\GameBuilder;
use Hexammon\GameWorker\Player;
use PHPUnit\Framework\TestCase;

class GameBuilderTest extends TestCase
{

    public function testBuildWithClassicRuleSet()
    {
        $builder = new GameBuilder();
        $boardConfig = new BoardConfig('hex', 4, 4);
        $playerA = new Player('A');
        $playerB = new Player('B');
        $players = [
            $playerA,
            $playerB,
        ];
        $game = $builder->build($players, $boardConfig);
        $this->assertCount(2, $game->getPlayers());

        $this->assertCount(1, $game->getBoard()->getTileByCoordinates('1.4')->getArmy());
        $this->assertSame($playerA, $game->getBoard()->getTileByCoordinates('1.4')->getArmy()->getOwner());
        $this->assertTrue($game->getBoard()->getTileByCoordinates('1.4')->hasCastle());

        $this->assertCount(1, $game->getBoard()->getTileByCoordinates('4.1')->getArmy());
        $this->assertSame($playerB, $game->getBoard()->getTileByCoordinates('4.1')->getArmy()->getOwner());
        $this->assertTrue($game->getBoard()->getTileByCoordinates('4.1')->hasCastle());

    }

}