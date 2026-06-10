#!/bin/sh
# ──────────────────────────────────────────────────────────────
#  TZLDashy entrypoint
#  1. Wait for MariaDB to be reachable
#  2. Import schema.sql only on a truly fresh database
#  3. Hand off to supervisord (nginx + php-fpm)
# ──────────────────────────────────────────────────────────────
set -e

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-tzldashy}"
DB_NAME="${DB_NAME:-tzldashy}"
SCHEMA="/tzldashy-init/schema.sql"

echo "[TZLDashy] ──────────────────────────────────────"
echo "[TZLDashy]  TZLDashy starting up"
echo "[TZLDashy] ──────────────────────────────────────"

# ── 1. Wait for MariaDB ───────────────────────────────────────
echo "[TZLDashy] Waiting for database at ${DB_HOST}:${DB_PORT}..."
RETRIES=0
MAX_RETRIES=60
until MYSQL_PWD="${DB_PASS}" mysql \
        -h"${DB_HOST}" -P"${DB_PORT}" \
        -u"${DB_USER}" \
        "${DB_NAME}" \
        -e "SELECT 1;" > /dev/null 2>&1; do
    RETRIES=$((RETRIES + 1))
    if [ "$RETRIES" -ge "$MAX_RETRIES" ]; then
        echo "[TZLDashy] ERROR: Database did not become ready after ${MAX_RETRIES} attempts. Exiting."
        exit 1
    fi
    echo "[TZLDashy]   Not ready yet (attempt ${RETRIES}/${MAX_RETRIES}), retrying in 3s..."
    sleep 3
done
echo "[TZLDashy] Database is ready."

# ── 2. Import schema only on a fresh database ─────────────────
TABLE_COUNT=$(MYSQL_PWD="${DB_PASS}" mysql \
    -h"${DB_HOST}" -P"${DB_PORT}" \
    -u"${DB_USER}" \
    -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}';" \
    2>/dev/null || echo "0")

# Trim whitespace
TABLE_COUNT=$(echo "$TABLE_COUNT" | tr -d '[:space:]')

if [ -z "$TABLE_COUNT" ] || [ "$TABLE_COUNT" = "0" ]; then
    echo "[TZLDashy] Fresh database detected — importing schema..."
    MYSQL_PWD="${DB_PASS}" mysql \
        -h"${DB_HOST}" -P"${DB_PORT}" \
        -u"${DB_USER}" \
        "${DB_NAME}" < "${SCHEMA}"
    echo "[TZLDashy] Schema imported successfully."
else
    echo "[TZLDashy] Database already initialized (${TABLE_COUNT} tables). Skipping schema import."
fi

echo "[TZLDashy] Starting nginx + php-fpm via supervisord..."
echo "[TZLDashy] ──────────────────────────────────────"

# ── 3. Start services ─────────────────────────────────────────
exec "$@"
