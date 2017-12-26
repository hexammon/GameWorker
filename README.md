# Hexammon Game Worker Micro Service

## Game WAMP API

Use `hexammon` realm. 

### Basic game data types:

#### coords

Sting in format `<x>.<y>`, where `x` and `y` is integer values from 1 to last board cell number. 

#### datetime

String in ISO-8601: YYYY-MM-DDThh:mm:ss[.sss]±hh:mm

#### Player

...TBD...

### RPC's

#### Public

Called by players clients.

`net.hexammon.game.<uuid>.assault, [assaulterArmyCoord: <coords>, attackedCastleCoords: <coords>]`

`net.hexammon.game.<uuid>.attack, [assaulterArmyCoord: <coords>, attackedArmyCoords: <coords>]`

`net.hexammon.game.<uuid>.build, [newCastleCoords: <coords>]`

`net.hexammon.game.<uuid>.move, [sourceArmyCoords: <coords>, targetArmyCoords: <coords>, numberOfUnirs<int>]`

`net.hexammon.game.<uuid>.replenish, [castleCoords: <coords>]`

`net.hexammon.game.<uuid>.takeoff, [castleCoords: <coords>]`

All RPC's above return `GameState` object with next structure:

```json
{
    "move": "<int>", // number of move in game
    "state": "during", 
    "lastAction": {
        "datetime": "<datetime>", // time of player do action
        "stepNumber": "<int>", // number of this action in move steps
        "stepsInMove": "<int>", // total number of step in move 
        "player": "<Player>",
        "action": "(assault|attack|build|move|replenish|takeoff)",
        "args": ["args of last called rpc"],
        "boardDiff": "<BoardDiff>" // changed after action cells
    },
    "nextAction": {
        "player": "<Player>",
        "stepNumber": "<int>", // number of this action in move steps
        "stepsInMove": "<int>" // total number of step in move
    }
}
```

Where `BoardDiff` -- aggregation of affected in last move tiles -- object with next structure:

```json
{
    "<coords>": "<Tile>",
    "<coords>": "<Tile>",
    "<coords>": "<Tile>"
}
```

#### Internal

Called by Game Dispatcher. 

`net.hexammon.game.create, [players: <Player>[], boardType: (hex|square), numberOfRows: <int>, numberOfColumns: <int>]`

Initialize game with players and board. Return structure with game info:

```json
{
    "uuid": "XXXX-XXXX-XXXX-XXXX",
    "players": "<Player>[]",
    "board": "<BoardDiff>" // full board representation after initial setting 
}
```


### Topics

`net.hexammon.game.<uuid>.update <GameState>` - updates after every step. 

On game over `GameState` looks like:

```json
{
    "move": 127, 
    "state": "over",
    "lastAction": {
        "datetime": "2017-07-17T00:21:12+03:00",
        "stepNumber": 3,
        "stepsInMove": 4,
        "player": {
            "uuid": "XXXX-XXXX-XXXX-XXXX",
            "nickname": "samizdam"
        },
        "action": "assault",
        "args": ["1.1", "1.2"],
        "boardDiff": {
            "1.1": {
                "hasCastle": true,
                "army": {
                    "player": {
                        "uuid": "XXXX-XXXX-XXXX-XXXX",
                        "nickname": "samizdam"                    
                    },
                    "units": 2
                }
            },
            "1.2": {
                "hasCastle": false,
                "army": null
            }
        }
    },
    "nextAction": null
}
```

## Work with docker

For build: see Makefile. 

For dev tools and scripts: see Makefile. 