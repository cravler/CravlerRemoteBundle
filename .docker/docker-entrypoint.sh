#!/bin/bash
set -e

: ${CRAVLER_REMOTE_ENV:=prod}
: ${CRAVLER_REMOTE_WORKDIR:=/var/www}
: ${CRAVLER_REMOTE_PATH:=vendor/cravler/remote-bundle/Cravler/RemoteBundle/Resources/nodejs}

/entrypoint.sh echo ''

while ! echo exit | nc "$CONF_HOST_PHP5_FPM" "$CONF_PORT_PHP5_FPM"; do
    echo ".";
    sleep 3;
done

if [ 'cravler_remote_run_app' != "$3" ]; then
    exec "$@"
else
    echo "Running Cravler Remote APP: $(date +"%d.%m.%Y %r")"
    echo ""
    if [ -d "$CRAVLER_REMOTE_WORKDIR/$CRAVLER_REMOTE_PATH" ]; then

        npm install --prefix "$CRAVLER_REMOTE_WORKDIR/$CRAVLER_REMOTE_PATH"
        sleep 1

        if [ -d "$CRAVLER_REMOTE_WORKDIR/app" ]; then

            sudo rm -rf "$CRAVLER_REMOTE_WORKDIR/app/cache/$CRAVLER_REMOTE_ENV"
            bash -c "php $CRAVLER_REMOTE_WORKDIR/app/console cravler:remote:server --env=$CRAVLER_REMOTE_ENV --no-debug" &
            sleep 1

            APP_SOURCE="$(php $CRAVLER_REMOTE_WORKDIR/app/console cravler:remote:ubuntu:upstart app)"
            NODEJS=$(echo "$APP_SOURCE" | sed -n 's/^env NODEJS="\(.*\)"$/\1/p')
            bash -c "$NODEJS $CRAVLER_REMOTE_WORKDIR/$CRAVLER_REMOTE_PATH/app.js"

        fi

    fi
fi