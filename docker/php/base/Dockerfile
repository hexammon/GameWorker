ARG SOURCE_DOCKER_IMAGE

FROM ${SOURCE_DOCKER_IMAGE}

COPY ./etc /etc

RUN apk update \
    && apk add supervisor \
    && rm -rf /var/cache/apk/*

WORKDIR /srv/game-worker

CMD /usr/bin/supervisord -c /etc/supervisor/game-worker.conf