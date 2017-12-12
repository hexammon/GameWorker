<?php

namespace Hexammon\GameWorker;

use Hexammon\HexoNards\Board\AbstractTile;
use Hexammon\HexoNards\Game\Action\AssaultCastle;
use Hexammon\HexoNards\Game\Action\AttackEnemy;
use Hexammon\HexoNards\Game\Action\BuildCastle;
use Hexammon\HexoNards\Game\Action\MergeArmy;
use Hexammon\HexoNards\Game\Action\MoveArmy;
use Hexammon\HexoNards\Game\Action\PlayerActionInterface;
use Hexammon\HexoNards\Game\Action\ReplenishGarrison;
use Hexammon\HexoNards\Game\Action\TakeOffEnemyGarrison;
use Lootils\Uuid\Uuid;
use Thruway\ClientSession;

class Router
{

    /**
     * @var Application
     */
    private $application;

    private $gameUUID;
    private $battleService;
    private $game;
    private $board;

    public function __construct(Application $application, Uuid $gameUUID)
    {
        $this->application = $application;
        $this->game = $application->getGame();
        $this->board = $this->game->getBoard();
        $this->battleService = $this->game->getRuleSet();
        $this->gameUUID = $gameUUID;
    }


    public function bindActions(ClientSession $session)
    {
        /**
         * TODO: unpack rpc arguments from lambda - right signature is function(array $args, array $argsKw, array $details)
         * TODO: use ['disclose_caller'=>true] everywhere in rpc registrations.
         */

        $session->register($this->buildUri('assault'), function (string $sourceCoods, string $targetCoords) {
            // TODO add check that army exists and castle exists
            $assaulterArmy = $this->getTileByCoordinates($sourceCoods)->getArmy();
            $castle = $this->getTileByCoordinates($targetCoords)->getCastle();
            $action = new AssaultCastle($castle, $assaulterArmy);
            // TODO try exceptions
            return $this->doActionAndGetResult($action);
        });

        $session->register($this->buildUri('attack'), function (string $sourceCoods, string $targetCoords) {
            // TODO add check that army exists
            $attackedArmy = $this->getTileByCoordinates($sourceCoods)->getArmy();
            $assaulterArmy = $this->getTileByCoordinates($targetCoords)->getArmy();
            $action = new AttackEnemy($assaulterArmy, $attackedArmy, $this->battleService);
            return $this->doActionAndGetResult($action);
        });

        $session->register($this->buildUri('build'), function (string $coords) {
            $tile = $this->getTileByCoordinates($coords);
            $action = new BuildCastle($tile);
            return $this->doActionAndGetResult($action);
        });

        $session->register($this->buildUri('merge'), function (string $sourceCoords, string $targetCoords) {
            $sourceTile = $this->getTileByCoordinates($sourceCoords);
            $targetTile = $this->getTileByCoordinates($targetCoords);
            $sourceArmy = $sourceTile->getArmy();
            $targetArmy = $targetTile->getArmy();

            $action = new MergeArmy($sourceArmy, $targetArmy);
            return $this->doActionAndGetResult($action);
        });

        /**
         * move
         * and merge?
         * TODO compact Merge and Move logic and put to Game with good unit tests?
         */
        $session->register($this->buildUri('move'),
            function (string $sourceCoods, string $targetCoords, int $units) {
                $sourceTile = $this->getTileByCoordinates($sourceCoods);
                $targetTile = $this->getTileByCoordinates($targetCoords);
                // TODO try exceptions
                if ($targetTile->hasArmy()) {
                    $sourceArmy = $sourceTile->getArmy();
                    $targerArmy = $targetTile->getArmy();
                    if ($units < $targerArmy->count()) {
                        $targerArmy = $targerArmy->divide($units);
                    }

                    $action = new MergeArmy($sourceArmy, $targerArmy);
                } else {
                    $action = new MoveArmy($sourceTile, $targetTile, $units);
                }
                return $this->doActionAndGetResult($action);
            });

        $session->register($this->buildUri('replenish'), function (string $coord) {
            $army = $this->getTileByCoordinates($coord)->getArmy();
            return $this->doActionAndGetResult(new ReplenishGarrison($army, 1));
        });

        $session->register($this->buildUri('takeoff'), function (string $coords) {
            $castle = $this->getTileByCoordinates($coords)->getCastle();
            $action = new TakeOffEnemyGarrison($castle);
            return $this->doActionAndGetResult($action);
        });

    }

    /**
     * TODO add wrapper and save previous state
     */
    private function getBoardDiff()
    {
        return $this->board;
    }

    private function getTileByCoordinates(string $coords): AbstractTile
    {
        return $this->board->getTileByCoordinates($coords);
    }


    private function buildUri(string $suffix): string
    {
        return sprintf('net.haxammon.%s.%s', $this->gameUUID->getUuid(), $suffix);
    }

    private function doActionAndGetResult(PlayerActionInterface $action)
    {
        $this->application->do($action);
        return $this->getBoardDiff();
    }
}