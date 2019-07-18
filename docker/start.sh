#!/bin/sh

echo "Starting MUM container..."
php /app/artisan migrate --env=production --force
apache2-foreground