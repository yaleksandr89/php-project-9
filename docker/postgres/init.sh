#!/usr/bin/env bash
set -e

until pg_isready -h db -p 5432 -U "$POSTGRES_USER" -d "$POSTGRES_DB"; do
  echo "Waiting for PostgreSQL..."
  sleep 1
done

if [ -f /app/database.sql ]; then
  PGPASSWORD="$POSTGRES_PASSWORD" psql \
    -h db \
    -U "$POSTGRES_USER" \
    -d "$POSTGRES_DB" \
    -f /app/database.sql
fi