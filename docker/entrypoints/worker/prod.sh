#!/bin/sh
set -e

timeoutSeconds=60
secondsElapsed=1

until PGPASSWORD=$DB_PASSWORD pg_isready --host $DB_HOST --port $DB_PORT --username $DB_USERNAME --dbname $DB_DATABASE; do
  sleep 1
  secondsElapsed=$(($secondsElapsed+1))

  if [ "$secondsElapsed" -ge "$timeoutSeconds" ]; then
    >&2 echo "Timed out while waiting for postgres after ${timeoutSeconds} seconds."
    exit 1
  fi
done

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
        set -- php-fpm "$@"
fi

exec "$@"
