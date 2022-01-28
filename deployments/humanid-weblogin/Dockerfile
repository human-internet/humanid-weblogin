## Stage 1: Build Assets
FROM node:14 as assets

COPY scss /usr/src/app/scss
COPY package.json /usr/src/app
COPY package-lock.json /usr/src/app

WORKDIR /usr/src/app

RUN npm install \
  && npm run prod

## Stage 2: Build runtime base
FROM php:7.4-apache as runtime-base

# Define dependencies
ENV BUILD_PKG_BASE="libzip-dev libonig-dev libgmp-dev" \
    BUILD_PKG_GD="libpng-dev libwebp-dev libjpeg62-turbo-dev libfreetype6-dev" \
    BUILD_PKG_INTL="zlib1g-dev libicu-dev g++"

# Install dependencies
RUN apt-get update \
  && apt-get install -y git unzip ${BUILD_PKG_BASE} ${BUILD_PKG_GD} ${BUILD_PKG_INTL}

# Install extensions
RUN docker-php-ext-configure gd --with-freetype --with-webp --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install bcmath exif gd intl opcache zip

# Clean up
RUN apt-get remove --purge -y ${BUILD_PKG_BASE} ${BUILD_PKG_GD} ${BUILD_PKG_INTL} \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/app

## Stage 3: Build Sources
FROM runtime-base as sources

COPY src /var/www/app
COPY --from=assets /usr/src/app/src/public/css/style.css  /var/www/app/public/css/style.css
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN composer install

## Stage 4: Build Runtime
FROM runtime-base

# Copy sources
COPY --from=sources /var/www/app /var/www/app

# Copy PHP config
COPY deployments/humanid-weblogin/mpm_prefork.conf /etc/apache2/mods-available/
COPY deployments/humanid-weblogin/000-default.conf /etc/apache2/sites-available/

# Enable modules
RUN if command -v a2enmod >/dev/null 2>&1; then \
      a2enmod expires headers rewrite \
    ;fi

# ----------
# Entrypoint
# ----------
# Set default user to www-data
USER 33

# Set default working directory
WORKDIR /var/www/app
