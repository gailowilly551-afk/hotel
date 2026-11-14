#!/bin/bash
set -e

# Support dynamic PORT provided by cloud environments (e.g. Render, Railway)
if [ -n "$PORT" ]; then
    sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf 2>/dev/null || true
    sed -i "s/:80>/:$PORT>/g" /etc/apache2/sites-available/*.conf 2>/dev/null || true
fi

exec "$@"
