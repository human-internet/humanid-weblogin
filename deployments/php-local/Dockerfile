FROM php:7.4-apache

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

# Copy PHP config
COPY 000-default.conf /etc/apache2/sites-available/

# Enable modules
RUN if command -v a2enmod >/dev/null 2>&1; then \
      a2enmod expires headers rewrite \
    ;fi

# --------------------
# For development only
# --------------------
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# ----------
# Entrypoint
# ----------
# Set default user to www-data
USER 33

# Set default working directory
WORKDIR /var/www/app
