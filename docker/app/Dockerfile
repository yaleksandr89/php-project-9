FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpq-dev \
    unzip \
    git \
    curl \
    postgresql-client \
    && docker-php-ext-install zip pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

CMD ["bash"]