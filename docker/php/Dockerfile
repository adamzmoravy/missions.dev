# Local development image for the Missions.dev PHP API.
# Matches production-ish runtime: PHP 7.4 (see .valetphprc / composer.json "platform") + Apache + mod_rewrite.
FROM php:7.4-apache

# php:7.4 images are based on Debian 11 "bullseye", which has moved to archive.debian.org.
RUN sed -i -e 's|http://deb.debian.org|http://archive.debian.org|g' \
           -e 's|http://security.debian.org|http://archive.debian.org|g' /etc/apt/sources.list \
    && echo 'Acquire::Check-Valid-Until "false";' > /etc/apt/apt.conf.d/99archive

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libicu-dev libxml2-dev \
        default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mysqli gd intl zip bcmath exif \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY php.ini /usr/local/etc/php/conf.d/zz-missions.ini
COPY vhost.conf /etc/apache2/sites-available/000-default.conf
COPY entrypoint.sh /usr/local/bin/missions-entrypoint
RUN chmod +x /usr/local/bin/missions-entrypoint

WORKDIR /var/www/html
ENTRYPOINT ["missions-entrypoint"]
CMD ["apache2-foreground"]
