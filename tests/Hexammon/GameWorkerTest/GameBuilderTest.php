<?php

namespace Hexammon\GameWorkerTest;

use Hexammon\GameWorker\BoardConfig;
use Hexammon\GameWorker\GameBuilder;
use Hexammon\GameWorker\Player;
use Hexammon\HexoNards\Game\Move\Random\RandomMoveGeneratorAdapter;
use Hexammon\HexoNards\Game\Move\Random\TwoDice;
use PHPUnit\Framework\TestCase;

class GameBuilderTest extends TestCase
{

    public function testBuildWithClassicRuleSet()
    {
        $builder = new GameBuilder();
        $boardConfig = new BoardConfig('hex', 4, 4);
        $player1 = new Player();
        $player2 = new Player();
        $players = [
            $player1,
            $player2,
        ];
        $game = $builder->build($players, $boardConfig, new RandomMoveGeneratorAdapter(new TwoDice()));
        $this->assertCount(2, $game->getPlayers());

        $this->assertCount(1, $game->getBoard()->getTileByCoordinates('1.4')->getArmy());
        $this->assertSame($player1, $game->getBoard()->getTileByCoordinates('1.4')->getArmy()->getOwner());
        $this->assertTrue($game->getBoard()->getTileByCoordinates('1.4')->hasCastle());

        $this->assertCount(1, $game->getBoard()->getTileByCoordinates('4.1')->getArmy());
        $this->assertSame($player2, $game->getBoard()->getTileByCoordinates('4.1')->getArmy()->getOwner());
        $this->assertTrue($game->getBoard()->getTileByCoordinates('4.1')->hasCastle());

    }

}