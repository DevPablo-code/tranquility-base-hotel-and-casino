FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite

COPY ./web/public /var/www/html
COPY ./web/config /var/www/config
COPY ./web/partials /var/www/partials
COPY ./web/lang/ /var/www/lang