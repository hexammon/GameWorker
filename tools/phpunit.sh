#!/usr/bin/env bash

source .env

docker run -it \
    --user $UID:$UID \
    --volume $(pwd):/srv/game-worker \
    ${DEV_DOCKER_IMAGE} vendor/bin/phpunit "$@"