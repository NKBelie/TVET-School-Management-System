FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    default-mysql-client \
    libmariadb-dev-compat \
    && docker-php-ext-install mysqli pdo pdo_mysql

COPY src/ /var/www/html/

RUN a2enmod rewrite

EXPOSE 80

CMD ["apache2-foreground"]