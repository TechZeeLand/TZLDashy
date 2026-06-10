###############################################################
#  TZLDashy — Combined App Image
#  PHP 8.2-FPM + Nginx 1.x — both run in this single container
#  managed by supervisord.
#  Image: ghcr.io/techzeeland/tzldashy:latest
###############################################################

FROM php:8.2-fpm-alpine

# ── System packages ────────────────────────────────────────────
# nginx        — web server (baked in, no separate container)
# supervisor   — process manager for nginx + php-fpm
# mysql-client — used by entrypoint.sh for DB health checks
RUN apk add --no-cache \
        bash curl \
        freetype-dev libjpeg-turbo-dev libpng-dev libwebp-dev \
        libzip-dev oniguruma-dev \
        zip unzip sudo \
        nginx supervisor mysql-client

# ── PHP extensions ─────────────────────────────────────────────
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
 && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql mbstring gd zip opcache

# ── Composer ───────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── PHP.ini production tweaks ──────────────────────────────────
RUN { \
        echo "upload_max_filesize = 50M"; \
        echo "post_max_size = 55M"; \
        echo "memory_limit = 256M"; \
        echo "max_execution_time = 60"; \
        echo "opcache.enable=1"; \
        echo "opcache.memory_consumption=128"; \
        echo "opcache.validate_timestamps=0"; \
    } >> /usr/local/etc/php/conf.d/tzldashy.ini

# ── Sudoers (allow www-data to run shutdown/reboot) ────────────
RUN echo "www-data ALL=(ALL) NOPASSWD: /sbin/shutdown, /sbin/reboot" \
        >> /etc/sudoers.d/www-data \
 && chmod 0440 /etc/sudoers.d/www-data

# ── App source ─────────────────────────────────────────────────
WORKDIR /app

# Install Composer deps (separate layer — cached unless composer.json changes)
COPY src/composer.json src/composer.lock* ./
RUN composer install \
        --no-dev \
        --optimize-autoloader \
        --no-interaction \
        2>/dev/null \
    || composer install --no-dev --no-interaction

# Copy full application source
COPY src/ ./

# ── Nginx configuration ────────────────────────────────────────
# Remove Alpine default vhost; install ours
RUN rm -f /etc/nginx/http.d/default.conf \
 && mkdir -p /etc/nginx/http.d
COPY docker/nginx/tzldashy.conf /etc/nginx/http.d/default.conf

# Ensure nginx pid directory exists and is writable
RUN mkdir -p /run/nginx

# ── Supervisor configuration ───────────────────────────────────
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN mkdir -p /var/log/supervisor

# ── Schema — embedded for zero-config first-boot init ─────────
RUN mkdir -p /tzldashy-init
COPY database/schema.sql /tzldashy-init/schema.sql

# ── Entrypoint ─────────────────────────────────────────────────
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# ── Permissions ────────────────────────────────────────────────
RUN mkdir -p public/uploads/avatars public/Logos \
 && chown -R www-data:www-data /app \
 && chmod -R 755 public/uploads public/Logos

# ── Expose ─────────────────────────────────────────────────────
EXPOSE 1011

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
