#!/bin/sh
# ─────────────────────────────────────────────────────────────────
#  TZLDashy entrypoint
#  1. Wait for MariaDB
#  2. Auto-import schema on fresh DB only
#  3. Hand off to CMD (supervisord)
# ─────────────────────────────────────────────────────────────────
set -e

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-tzldashy}"
DB_NAME="${DB_NAME:-tzldashy}"
SCHEMA="/tzldashy-init/schema.sql"

echo "[TZLDashy] ──────────────────────────────────────"
echo "[TZLDashy] Starting TZLDashy"
echo "[TZLDashy] ──────────────────────────────────────"

# ── 1. Wait for MariaDB ──────────────────────────────────────────
echo "[TZLDashy] Waiting for database at ${DB_HOST}:${DB_PORT}..."
RETRIES=0
until MYSQL_PWD="${DB_PASS}" mysql \
        -h"${DB_HOST}" -P"${DB_PORT}" \
        -u"${DB_USER}" \
        "${DB_NAME}" -e "SELECT 1;" > /dev/null 2>&1; do
    RETRIES=$((RETRIES+1))
    [ "$RETRIES" -ge 60 ] && echo "[TZLDashy] DB not ready after 60 tries. Exiting." && exit 1
    sleep 3
done
echo "[TZLDashy] Database ready."

# ── 2. Import schema on a fresh DB ──────────────────────────────
TABLE_COUNT=$(MYSQL_PWD="${DB_PASS}" mysql \
    -h"${DB_HOST}" -P"${DB_PORT}" \
    -u"${DB_USER}" -N \
    -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}';" 2>/dev/null | tr -d '[:space:]')

if [ -z "$TABLE_COUNT" ] || [ "$TABLE_COUNT" = "0" ]; then
    echo "[TZLDashy] Fresh database — importing schema..."
    MYSQL_PWD="${DB_PASS}" mysql \
        -h"${DB_HOST}" -P"${DB_PORT}" \
        -u"${DB_USER}" \
        "${DB_NAME}" < "${SCHEMA}"
    echo "[TZLDashy] Schema imported."
else
    echo "[TZLDashy] Database already has ${TABLE_COUNT} tables — skipping import."
fi

echo "[TZLDashy] Starting supervisord..."
exec "$@"
