<?php

namespace Hexammon\GameWorker;

use Hexammon\HexoNards\Board\BoardBuilder;
use Hexammon\HexoNards\Game\Game;
use Hexammon\HexoNards\Game\Rules\ClassicRuleSet;
use Hexammon\HexoNards\Game\Rules\RuleSetInterface;

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

    public function build(array $players, BoardConfig $boardConfig, RuleSetInterface $ruleSet = null): Game
    {
        $ruleSet = $ruleSet ?: new ClassicRuleSet();
        $board = $this->boardBuilder->build($boardConfig->getType(), $boardConfig->getRowsCount(),
            $boardConfig->getColumnsCount());
        $game = new Game($players, $board, $ruleSet);
        $ruleSet->getInitialSetting()->arrangePieces($game);
        return $game;
    }
}