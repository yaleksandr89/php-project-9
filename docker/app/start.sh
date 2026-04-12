#!/usr/bin/env bash
set -e

git config --global --add safe.directory /app
composer install
php -S 0.0.0.0:8000 -t public