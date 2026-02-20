FROM php:8.2-apache


RUN docker-php-ext-install pdo_mysql mysqli


RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/* \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer

# Включение mod_rewrite для Apache
RUN a2enmod rewrite

WORKDIR /var/www/html