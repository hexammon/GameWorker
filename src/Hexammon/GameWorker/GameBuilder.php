<?php


namespace Hexammon\GameWorker;


use Hexammon\HexoNards\Board\BoardBuilder;
use Hexammon\HexoNards\Game\Game;
use Hexammon\HexoNards\Game\Move\MoveGeneratorInterface;

class GameBuilder
{

    /**
     * @var BoardBuilder
     */
    private $boardBuilder;

    public function __construct(BoardBuilder $boardBuilder = null)
    {
        $this->boardBuilder = $boardBuilder ?: new BoardBuilder();
    }

    public function build(array $players, BoardConfig $boardConfig, MoveGeneratorInterface $moveGenerator): Game
    {
        $board = $this->boardBuilder->build($boardConfig->getType(), $boardConfig->getRowsCount(),
            $boardConfig->getColumnsCount());
        return new Game($players, $board, $moveGenerator);
    }
}