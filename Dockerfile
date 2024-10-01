FROM php:8.3-apache-bookworm AS base

RUN useradd -ms /bin/bash php_user

#RUN apt-get update && apt-get install -y \
#    libicu-dev \
#    && docker-php-ext-install \
#    ctype \
#    iconv \
#    pcre \
#    session \
#    simplexml \
#    tokenizer \
#    && apt-get clean \
#    && rm -rf /var/lib/apt/lists/*

RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

RUN a2enmod rewrite \
  && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

RUN chown -R php_user:php_user /var/www/html

USER php_user

FROM base AS build-production

COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-scripts

FROM php:8.3-apache-bookworm AS production

COPY --from=build-production /var/www/html/vendor /var/www/html/vendor

COPY . .