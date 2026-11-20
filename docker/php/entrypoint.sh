#!/bin/sh
set -e
cd /var/www/html

# 1. config.env (gitignored) - create from the Docker template on first run.
if [ ! -f config.env ]; then
    echo "[missions] creating config.env from docker/config.env.docker"
    secret=$(php -r 'echo bin2hex(random_bytes(16));')
    sed "s/__GENERATED__/${secret}/" docker/config.env.docker > config.env
fi

# 2. PHP dependencies. There is no composer.lock in the repo (*.lock is gitignored),
#    so resolve against the PHP 7.4 platform pin; --no-dev because mockery dev-main needs PHP 8.
#    --no-security-blocking: most pinned (EOL) libraries carry Packagist security advisories, which
#    Composer refuses by default. LOCAL DEVELOPMENT ONLY - never deploy a build made this way.
if [ ! -f backend/vendor/autoload.php ]; then
    echo "[missions] installing backend composer dependencies (first run takes a few minutes)"
    composer update --working-dir=backend --no-dev --no-security-blocking --no-interaction --prefer-dist --no-progress
fi

# 3. Writable dirs and the (gitignored) document root.
mkdir -p public_html backend/cache backend/temp
# API entry point lives at public_html/api/ in production (copied there by `vite build`).
if [ ! -f public_html/api/index.php ]; then
    mkdir -p public_html/api
    cp -R frontend/public/api/. public_html/api/
fi
chmod -R a+rwX backend/cache backend/temp 2>/dev/null || true

exec docker-php-entrypoint "$@"
