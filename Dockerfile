FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql \
    && php -r 'exit(extension_loaded("PDO") && extension_loaded("pdo_mysql") ? 0 : 1);' \
    && a2enmod rewrite

COPY docker/apache-cozycafe.conf /etc/apache2/conf-available/cozycafe.conf
RUN a2enconf cozycafe

WORKDIR /var/www/html
COPY . /var/www/html

RUN mkdir -p /var/www/html/public/uploads \
    && chown -R www-data:www-data /var/www/html/public/uploads \
    && chmod 775 /var/www/html/public/uploads

EXPOSE 80
