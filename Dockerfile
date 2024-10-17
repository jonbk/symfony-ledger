FROM php:8.3-apache-bookworm AS base

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install \
    intl \
    opcache \
    pdo_pgsql \
    zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

RUN a2enmod rewrite \
  && sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
  && sed -i 's/*:80/*:8080/' /etc/apache2/sites-available/000-default.conf \
  && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

RUN useradd -ms /bin/bash php_user && \
    mkdir -p /var/www/html && \
    chown -R php_user:php_user /var/www/html

USER php_user

WORKDIR /var/www/html

EXPOSE 8080

FROM base AS production

USER root

COPY docker-php-entrypoint /usr/local/bin/

RUN { \
    echo "memory_limit=128M"; \
    echo "opcache.memory_consumption=256"; \
    echo "opcache.max_accelerated_files=20000"; \
    echo "opcache.validate_timestamps=0"; \
    echo "opcache.preload=/var/www/html/config/preload.php"; \
    echo "opcache.preload_user=php_user"; \
} > /usr/local/etc/php/conf.d/zzz-docker-php-custom.ini

USER php_user

ENV APACHE_RUN_USER=php_user

ENV APACHE_RUN_GROUP=php_user

COPY --chown=php_user:php_user . .

RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

ENTRYPOINT ["docker-php-entrypoint"]

CMD ["apache2-foreground"]

