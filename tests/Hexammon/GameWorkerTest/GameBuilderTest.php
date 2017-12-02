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

    public function testBuild()
    {
        $builder = new GameBuilder();
        $boardConfig = new BoardConfig('hex', 4, 4);
        $players = [
            new Player(),
            new Player(),
        ];
        $game = $builder->build($players, $boardConfig, new RandomMoveGeneratorAdapter(new TwoDice()));
        $this->assertCount(2, $game->getPlayers());
    }

}