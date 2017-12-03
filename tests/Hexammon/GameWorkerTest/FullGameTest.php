<?php

namespace Hexammon\GameWorkerTest;

use Hexammon\GameWorker\Application;
use Hexammon\GameWorker\BoardConfig;
use Hexammon\GameWorker\GameBuilder;
use Hexammon\GameWorker\Player;
use Hexammon\HexoNards\Game\Action\AttackEnemy;
use Hexammon\HexoNards\Game\Action\MoveArmy;
use Hexammon\HexoNards\Game\Action\ReplenishGarrison;
use Hexammon\HexoNards\Game\Move\Random\RandomInterface;
use Hexammon\HexoNards\Game\Move\Random\RandomMoveGeneratorAdapter;
use PHPUnit\Framework\TestCase;

class FullGameTest extends TestCase
{

    /**
     * Note: this is test scenario for short and simple, but full and strongly classic rules based game.
     * See
     */
    public function testGame()
    {
        $playerA = new Player();
        $playerB = new Player();
        $game = (new GameBuilder())->build([
            $playerA,
            $playerB,
        ],
            /** @example 2017-07-17T00:21:12+03:00 3.3 6 */
            new BoardConfig('hex', 3, 3),
            new RandomMoveGeneratorAdapter(new class implements RandomInterface
            {
                public function random(): int
                {
                    return 1;
                }
            }));
        $application = new Application($game);

        /** @example 2017-07-17T00:22:12+03:00 A + 1.3 # get unit playerA */
        $army = $game->getBoard()->getTileByCoordinates('1.3')->getArmy();
        $replenishAction = new ReplenishGarrison($army, 1);
        $application->do($replenishAction);
        $this->assertCount(2, $army);

        /** @example 2017-07-17T00:22:12+03:00 B + 3.1 # get unit playerB */
        $army = $game->getBoard()->getTileByCoordinates('3.1')->getArmy();
        $replenishAction = new ReplenishGarrison($army, 1);
        $application->do($replenishAction);
        $this->assertCount(2, $army);

        /** @example 2017-07-17T00:22:12+03:00 A + 1.3 # get unit playerA */
        $army = $game->getBoard()->getTileByCoordinates('1.3')->getArmy();
        $replenishAction = new ReplenishGarrison($army, 1);
        $application->do($replenishAction);
        $this->assertCount(3, $army);

        /** @example 2017-07-17T00:22:12+03:00 B 1 3.1>3.2 # playerB move army */
        $sourceTile = $game->getBoard()->getTileByCoordinates('3.1');
        $targetTile = $game->getBoard()->getTileByCoordinates('3.2');
        $move = new MoveArmy($sourceTile, $targetTile, 1);
        $application->do($move);
        $this->assertCount(1, $sourceTile->getArmy());
        $this->assertCount(1, $targetTile->getArmy());

        /** @example 2017-07-17T00:22:12+03:00 A + 1.3 # playerA get unit */
        $army = $game->getBoard()->getTileByCoordinates('1.3')->getArmy();
        $replenishAction = new ReplenishGarrison($army, 1);
        $application->do($replenishAction);
        $this->assertCount(4, $army);

        /** @example 2017-07-17T00:22:12+03:00 B 1 3.2>3.3 # playerB move army */
        $sourceTile = $game->getBoard()->getTileByCoordinates('3.2');
        $targetTile = $game->getBoard()->getTileByCoordinates('3.3');
        $move = new MoveArmy($sourceTile, $targetTile);
        $application->do($move);
        $this->assertFalse($sourceTile->hasArmy());
        $this->assertTrue($targetTile->hasArmy());
        $this->assertCount(1, $targetTile->getArmy());

        /** @example 2017-07-17T00:22:12+03:00 A + 1.3 # playerA get unit */
        $army = $game->getBoard()->getTileByCoordinates('1.3')->getArmy();
        $replenishAction = new ReplenishGarrison($army, 1);
        $application->do($replenishAction);
        $this->assertCount(5, $army);

        /** @example 2017-07-17T00:22:12+03:00 B 1 3.3>3.3 # playerB move army */
        $sourceTile = $game->getBoard()->getTileByCoordinates('3.3');
        $targetTile = $game->getBoard()->getTileByCoordinates('2.3');
        $move = new MoveArmy($sourceTile, $targetTile);
        $application->do($move);
        $this->assertFalse($sourceTile->hasArmy());
        $this->assertTrue($targetTile->hasArmy());

        /** @example 2017-07-17T00:28:12+03:00 A 1.3^2.3 # PlayerA attack */
        $armyOfPlayerA = $game->getBoard()->getTileByCoordinates('1.3')->getArmy();
        $armyOfPlayerB = $game->getBoard()->getTileByCoordinates('2.3')->getArmy();
        $attackAction = new AttackEnemy($armyOfPlayerA, $armyOfPlayerB);
        $application->do($attackAction);
        $this->assertCount(4, $armyOfPlayerA);
        $this->assertTrue($armyOfPlayerA->getTile()->hasCastle());
        $this->assertTrue($armyOfPlayerB->isDestroyed());

        /** @example 2017-07-17T00:28:12+03:00 B 2.2^1.1 # PlayerB get unit */
        $army = $game->getBoard()->getTileByCoordinates('3.1')->getArmy();
        $replenishAction = new ReplenishGarrison($army, 1);
        $application->do($replenishAction);
        $this->assertCount(2, $army);

        /** @example 2017-07-17T00:22:12+03:00 A + 1.3 # playerA get unit */
        $army = $game->getBoard()->getTileByCoordinates('1.3')->getArmy();
        $replenishAction = new ReplenishGarrison($army, 1);
        $application->do($replenishAction);
        $this->assertCount(5, $army);

        /** @example 2017-07-17T00:22:12+03:00 B 1 3.1>3.2 # playerB move army */
        $sourceTile = $game->getBoard()->getTileByCoordinates('3.1');
        $targetTile = $game->getBoard()->getTileByCoordinates('3.2');
        $move = new MoveArmy($sourceTile, $targetTile, 1);
        $application->do($move);
        $this->assertCount(1, $sourceTile->getArmy());
        $this->assertCount(1, $targetTile->getArmy());

        /** @example 2017-07-17T00:22:12+03:00 A + 1.3 # playerA get unit */
        $army = $game->getBoard()->getTileByCoordinates('1.3')->getArmy();
        $replenishAction = new ReplenishGarrison($army, 1);
        $application->do($replenishAction);
        $this->assertCount(6, $army);
        $this->markTestIncomplete('Play to game over. ');
    }

}