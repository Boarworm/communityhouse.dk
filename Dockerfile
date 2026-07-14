FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    zip \
    unzip \
    git \
    curl \
    mariadb-client \
    libmagickwand-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    gd \
    mbstring \
    zip \
    intl \
    xml \
    && pecl install imagick redis \
    && docker-php-ext-enable imagick redis

RUN echo "memory_limit=512M\nmax_execution_time=600\nupload_max_filesize=64M\npost_max_size=64M" > /usr/local/etc/php/conf.d/memory.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js and NPM
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

# Set working directory
WORKDIR /var/www
