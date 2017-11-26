#!/usr/bin/env bash

source .env

mkdir -p $HOME/.composer/cache/

docker run --rm --interactive --tty \
    --user $UID:$UID \
    --volume /etc/passwd:/etc/passwd:ro \
    --volume /etc/group:/etc/group:ro \
    --volume $PWD:/srv/game-worker \
    --volume $HOME/.composer:/tmp/.composer \
    --env COMPOSER_HOME=/tmp/.composer \
    ${DEV_DOCKER_IMAGE} composer $@
