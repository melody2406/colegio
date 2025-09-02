# Dockerfile
FROM php:8.3-apache

# Instala extensiones
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copia archivos del proyecto
COPY . /var/www/html/

# Establece permisos (opcional)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Activa mod_rewrite
RUN a2enmod rewrite

# Añade configuración UTF-8 global en apache2.conf
RUN echo "AddDefaultCharset UTF-8" >> /etc/apache2/apache2.conf
