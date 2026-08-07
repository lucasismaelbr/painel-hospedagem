FROM php:8.2-apache

# Instala dependências do sistema e extensões do PHP para Laravel + MySQL
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    curl \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Habilita o mod_rewrite do Apache
RUN a2enmod rewrite

# Corrige aviso de ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Sobrescreve o mpm_prefork.conf diretamente no local canônico lido pelo Apache
# O padrão é 256 workers - muito alto para o free tier com 512MB RAM
RUN printf '<IfModule mpm_prefork_module>\n  StartServers 2\n  MinSpareServers 2\n  MaxSpareServers 5\n  MaxRequestWorkers 25\n  MaxConnectionsPerChild 500\n</IfModule>\n' > /etc/apache2/mods-available/mpm_prefork.conf

# Aponta o DocumentRoot do Apache para a pasta /public do Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/conf-available/*.conf

ENV PORT 80
EXPOSE 80

WORKDIR /var/www/html

# Copia o código do projeto
COPY . /var/www/html

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissões de escrita e de execução do script
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod +x /var/www/html/entrypoint.sh

# Executa o entrypoint.sh que abre a porta 80 na hora
CMD ["/var/www/html/entrypoint.sh"]
