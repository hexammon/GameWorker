<?php

namespace Hexammon\GameWorker;

use Hexammon\HexoNards\Game\Game;

class Application
{

    /**
     * @var Game
     */
    private $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function getGame(): Game
    {
        return $this->game;
    }
}