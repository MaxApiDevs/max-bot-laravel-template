#!/bin/sh
set -eu

cd /var/www

if [ ! -f artisan ]; then
  echo "Creating a fresh Laravel 12 project..."
  rm -rf /tmp/laravel-app
  composer create-project laravel/laravel:^12.0 /tmp/laravel-app
  cp -a /tmp/laravel-app/. /var/www/
fi

if [ ! -f .env ]; then
  cp .env.example .env
fi

set_env() {
  key="$1"
  value="$2"

  if grep -q "^${key}=" .env; then
    sed -i "s|^${key}=.*|${key}=${value}|" .env
  else
    printf '\n%s=%s\n' "$key" "$value" >> .env
  fi
}

set_env "APP_ENV" "${APP_ENV:-local}"
set_env "APP_DEBUG" "${APP_DEBUG:-true}"
set_env "APP_URL" "${APP_URL:-http://localhost:8000}"
set_env "DB_CONNECTION" "${DB_CONNECTION:-mysql}"
set_env "DB_HOST" "${DB_HOST:-db}"
set_env "DB_PORT" "${DB_PORT:-3306}"
set_env "DB_DATABASE" "${DB_DATABASE:-laravel}"
set_env "DB_USERNAME" "${DB_USERNAME:-laravel}"
set_env "DB_PASSWORD" "${DB_PASSWORD:-laravel}"

if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --prefer-dist
fi

php artisan key:generate --force

echo "Waiting for database connection..."
until mysqladmin ping \
  --host="${DB_HOST:-db}" \
  --port="${DB_PORT:-3306}" \
  --user="${DB_USERNAME:-laravel}" \
  --password="${DB_PASSWORD:-laravel}" \
  --silent; do
  sleep 2
done

php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port=8000
