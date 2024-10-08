FROM php:8.3-apache-bookworm AS base

RUN useradd -ms /bin/bash php_user

RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    zip \
    && docker-php-ext-install  \
    opcache \
    pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

RUN a2enmod rewrite \
  && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

RUN chown -R php_user:php_user /var/www/html

USER php_user

FROM base AS production

USER root

COPY --chown=php_user:php_user composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY --chown=php_user:php_user . .

RUN echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.preload=/var/www/html/config/preload.php" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.preload_user=php_user" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

COPY docker-php-entrypoint /usr/local/bin/

USER php_user

ENTRYPOINT ["docker-php-entrypoint"]

CMD ["apache2-foreground"]

