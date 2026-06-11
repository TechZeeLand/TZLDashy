###############################################################
#  TZLDashy — Single-container image
#  PHP 8.2 FPM + Nginx, served from port 1011
#  Process supervisor: supervisord
###############################################################

FROM php:8.2-fpm-alpine

# ── System packages ──────────────────────────────────────────
RUN apk add --no-cache \
      nginx \
      supervisor \
      sudo \
      curl \
      freetype-dev \
      libjpeg-turbo-dev \
      libpng-dev \
      libwebp-dev \
      libxml2-dev \
      mariadb-client \
      tzdata \
    && docker-php-ext-configure gd \
         --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
         gd pdo pdo_mysql opcache

# ── PHP configuration ─────────────────────────────────────────
RUN { \
      echo "opcache.enable=1"; \
      echo "opcache.enable_cli=0"; \
      echo "opcache.memory_consumption=128"; \
      echo "opcache.max_accelerated_files=8000"; \
      echo "opcache.validate_timestamps=1"; \
      echo "opcache.revalidate_freq=2"; \
      echo "upload_max_filesize=32M"; \
      echo "post_max_size=34M"; \
      echo "memory_limit=256M"; \
      echo "max_execution_time=60"; \
    } > /usr/local/etc/php/conf.d/tzldashy.ini

# ── PHP-FPM pool ──────────────────────────────────────────────
RUN { \
      echo "[www]"; \
      echo "user = www-data"; \
      echo "group = www-data"; \
      echo "listen = 127.0.0.1:9000"; \
      echo "pm = dynamic"; \
      echo "pm.max_children = 20"; \
      echo "pm.start_servers = 4"; \
      echo "pm.min_spare_servers = 2"; \
      echo "pm.max_spare_servers = 8"; \
    } > /usr/local/etc/php-fpm.d/www.conf

# ── Sudo: allow www-data to reboot / shutdown ─────────────────
RUN echo "www-data ALL=(ALL) NOPASSWD: /sbin/reboot,/sbin/poweroff,/sbin/shutdown,/bin/systemctl" \
      >> /etc/sudoers.d/www-data \
    && chmod 0440 /etc/sudoers.d/www-data

# ── Application source ────────────────────────────────────────
WORKDIR /app
COPY src/ ./

# ── Directory permissions ─────────────────────────────────────
RUN mkdir -p \
      /app/public/uploads/avatars \
      /app/public/Logos \
      /tmp/php_sessions \
    && chown -R www-data:www-data /app /tmp/php_sessions \
    && chmod -R 755 /app/public

# ── Nginx config ──────────────────────────────────────────────
COPY docker/nginx/tzldashy.conf /etc/nginx/http.d/default.conf
RUN rm -f /etc/nginx/http.d/default.conf.bak 2>/dev/null; true

# ── Supervisord config ────────────────────────────────────────
COPY docker/supervisord.conf /etc/supervisor/conf.d/tzldashy.conf

# ── Entrypoint (schema auto-import on first boot) ─────────────
COPY docker/entrypoint.sh /entrypoint.sh
COPY database/schema.sql  /schema.sql
RUN chmod +x /entrypoint.sh

EXPOSE 1011

ENTRYPOINT ["/entrypoint.sh"]
