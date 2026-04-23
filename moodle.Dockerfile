# Build stage for Moodle
FROM php:8.3-fpm AS builder
WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    libpq-dev \
    libxml2-dev \
    libgd-dev \
    libintl-dev \
    libzip-dev \
    zlib1g-dev \
    libssl-dev \
    npm \
    nodejs \
    && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    pdo_mysql \
    mysqli \
    intl \
    zip \
    gd \
    soap \
    xml \
    ctype \
    json \
    hash \
    fileinfo \
    curl \
    mbstring \
    iconv \
    openssl

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy the repository
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install Node.js dependencies
RUN npm ci --production

# Build assets
RUN npm run update-packages 2>/dev/null || true

# Production stage
FROM php:8.3-fpm-alpine
WORKDIR /app

# Install runtime dependencies only
RUN apk add --no-cache \
    postgresql-client \
    mysql-client \
    curl \
    gettext \
    libxml2 \
    libgd \
    libintl \
    libzip \
    openssl \
    nginx \
    supervisor

# Install required PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    pdo_mysql \
    mysqli \
    intl \
    zip \
    gd \
    soap \
    xml \
    ctype \
    json \
    hash \
    fileinfo \
    curl \
    mbstring \
    iconv \
    openssl

# Copy optimized PHP config
RUN echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/moodle.ini && \
    echo "upload_max_filesize = 200M" >> /usr/local/etc/php/conf.d/moodle.ini && \
    echo "post_max_size = 200M" >> /usr/local/etc/php/conf.d/moodle.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/moodle.ini && \
    echo "max_input_time = 300" >> /usr/local/etc/php/conf.d/moodle.ini

# Copy from builder
COPY --from=builder /app /app

# Create necessary directories
RUN mkdir -p /var/www/moodledata && \
    chmod 777 /var/www/moodledata && \
    mkdir -p /app/public && \
    chmod 755 /app/public

# Configure Nginx
RUN mkdir -p /etc/nginx/conf.d && \
    echo 'server {
    listen 80;
    server_name _;
    root /app/public;
    index index.php;

    client_max_body_size 200M;

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    location ~ ^/(lib|pix|plugin)/ {
        expires 30d;
    }
}' > /etc/nginx/conf.d/moodle.conf

# Configure Supervisor to manage both PHP-FPM and Nginx
RUN mkdir -p /etc/supervisor/conf.d && \
    echo '[supervisord]
nodaemon=true
logfile=/var/log/supervisord.log
pidfile=/var/run/supervisord.pid

[program:php-fpm]
command=/usr/local/sbin/php-fpm
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true

[program:nginx]
command=/usr/sbin/nginx -g "daemon off;"
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true' > /etc/supervisor/conf.d/moodle.conf

# Set permissions
RUN chown -R www-data:www-data /app /var/www/moodledata

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/index.php || exit 1

# Expose port
EXPOSE 80

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
