FROM php:8.2-apache

RUN docker-php-ext-install -j$(nproc) mysqli

RUN a2enmod rewrite

# App lives in Apache document root
WORKDIR /var/www/html

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
