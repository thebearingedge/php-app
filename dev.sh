#!/bin/bash

set -a

if [ ! -f ./.env ]; then
  echo 'Error: ".env" file is required. Start by copying ".env.example".' 1>&2
  exit 1
fi

source .env

set +a

php -S "$PHP_HOST:$PHP_PORT" -t "server/public" -c "php.ini"
