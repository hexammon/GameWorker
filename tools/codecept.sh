#!/usr/bin/env bash

#source .env

docker exec --interactive --tty \
    --user $UID:$UID \
    hexammon-game-worker vendor/bin/codecept $@
