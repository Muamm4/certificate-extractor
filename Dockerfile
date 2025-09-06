FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    sqlite-dev \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql pdo_pgsql pdo_sqlite zip bcmath opcache exif pcntl

# Clear cache
RUN rm -rf /var/cache/apk/*

# Set working directory
WORKDIR /var/www/html

# Copy composer.lock and composer.json
COPY composer.lock composer.json .

# Install composer dependencies
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the rest of the application code
COPY . .

# Install NPM dependencies and build assets
RUN composer install
RUN npm install && npm run build

# Expose port 9000 for PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
