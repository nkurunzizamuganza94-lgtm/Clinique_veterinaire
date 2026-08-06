FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_mysql
COPY clinique_veterinaire/ /var/www/html/
RUN a2enmod rewrite
EXPOSE 80
