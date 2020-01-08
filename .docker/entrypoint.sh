#!/bin/bash

#On error no such file entrypoint.sh, execute in terminal - dos2unix .docker\entrypoint.sh

npm config set cache /var/www/.npm-cache --global
cd /var/www/frontend && npm install

cd /var/www/backend || exit
if [ ! -f .env.testing ]; then
  cp .env.testing.example .env.testing;
fi
if [ ! -f .env ]; then
  cp .env.example .env;
fi
chmod 777 -R ./storage
composer install
php artisan key:generate
php artisan migrate

php-fpm
