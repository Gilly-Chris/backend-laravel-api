FROM php:8.1-apache

# Arguments defined in docker-compose.yml
ARG user
ARG uid
ENV APACHE_DOCUMENT_ROOT /var/www/public
# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    wget \
    unzip \
    && a2enmod rewrite \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf


# Change to tmp to get composer installer
WORKDIR /tmp/composer
RUN wget -O composer-setup.php https://getcomposer.org/installer \
	&& php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
   

# Install PHP extensions
RUN docker-php-ext-install mbstring exif pcntl bcmath gd

RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli


# Set working directory
WORKDIR /var/www/

