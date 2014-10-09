#!/bin/bash

set -e

: ${CONF_HOST_PHP5_FPM:=php}; export CONF_HOST_PHP5_FPM
: ${CONF_PORT_PHP5_FPM:=9000}; export CONF_PORT_PHP5_FPM
: ${CRAVLER_REMOTE_ENV:=prod}; export CRAVLER_REMOTE_ENV
: ${CRAVLER_REMOTE_WORKDIR:=/var/www}; export CRAVLER_REMOTE_WORKDIR
: ${CRAVLER_REMOTE_PATH:=vendor/cravler/remote-bundle/Cravler/RemoteBundle/Resources/nodejs}; export CRAVLER_REMOTE_PATH
