###############################################################
#  TZLDashy — Single-container image
#  PHP 8.2 FPM + Nginx — managed by supervisord — port 1011
###############################################################

FROM php:8.2-fpm-alpine

# ── System packages ───────────────────────────────────────────
RUN apk add --no-cache \
        bash curl \
        freetype-dev libjpeg-turbo-dev libpng-dev libwebp-dev \
        libzip-dev oniguruma-dev \
        zip unzip sudo \
        nginx supervisor mysql-client

# ── PHP extensions ────────────────────────────────────────────
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
 && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql mbstring gd zip opcache

# ── PHP.ini tweaks ────────────────────────────────────────────
RUN { \
        echo "upload_max_filesize = 50M"; \
        echo "post_max_size = 55M"; \
        echo "memory_limit = 256M"; \
        echo "max_execution_time = 60"; \
        echo "opcache.enable=1"; \
        echo "opcache.memory_consumption=128"; \
        echo "opcache.validate_timestamps=1"; \
        echo "log_errors=On"; \
        echo "error_log=/proc/self/fd/2"; \
    } >> /usr/local/etc/php/conf.d/tzldashy.ini

# ── Sudoers: allow www-data to reboot / shutdown ──────────────
RUN echo "www-data ALL=(ALL) NOPASSWD: /sbin/shutdown,/sbin/reboot,/sbin/poweroff,/bin/systemctl" \
        >> /etc/sudoers.d/www-data \
 && chmod 0440 /etc/sudoers.d/www-data

# ── Application source ────────────────────────────────────────
WORKDIR /app
COPY src/ ./

# ── Nginx: remove default vhost, install ours ─────────────────
RUN rm -f /etc/nginx/http.d/default.conf \
 && mkdir -p /etc/nginx/http.d /run/nginx
COPY docker/nginx/tzldashy.conf /etc/nginx/http.d/default.conf

# ── Supervisor ────────────────────────────────────────────────
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN mkdir -p /var/log/supervisor

# ── Schema for zero-config first-boot init ───────────────────
RUN mkdir -p /tzldashy-init
COPY database/schema.sql /tzldashy-init/schema.sql

# ── Entrypoint ────────────────────────────────────────────────
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# ── Permissions ───────────────────────────────────────────────
RUN mkdir -p public/uploads/avatars public/Logos \
 && chown -R www-data:www-data /app \
 && chmod -R 755 public/uploads public/Logos

EXPOSE 1011

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
