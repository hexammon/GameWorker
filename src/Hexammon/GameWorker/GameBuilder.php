<?php


namespace Hexammon\GameWorker;


use Hexammon\HexoNards\Board\BoardBuilder;
use Hexammon\HexoNards\Game\Game;
use Hexammon\HexoNards\Game\Move\MoveGeneratorInterface;
use Hexammon\HexoNards\Game\Rules\ClassicRuleSet;
use Hexammon\HexoNards\Game\Rules\RuleSetInterface;

class GameBuilder
{

    /**
     * @var BoardBuilder
     */
    private $boardBuilder;
    /**
     * @var RuleSetInterface
     */
    private $ruleSet;

    public function __construct(RuleSetInterface $ruleSet = null, BoardBuilder $boardBuilder = null)
    {
        $this->ruleSet = $ruleSet ?: new ClassicRuleSet();
        $this->boardBuilder = $boardBuilder ?: new BoardBuilder();
    }

    public function build(array $players, BoardConfig $boardConfig, MoveGeneratorInterface $moveGenerator): Game
    {
        $board = $this->boardBuilder->build($boardConfig->getType(), $boardConfig->getRowsCount(),
            $boardConfig->getColumnsCount());
        $game = new Game($players, $board, $moveGenerator);
        $this->ruleSet->getInitialSetting()->arrangePieces($game);
        return $game;
    }
}