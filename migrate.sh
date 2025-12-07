#!/bin/bash

echo "Running Chill OKR migrations..."

DB_USER="root"
DB_NAME="okr_app"

for migration in migrations/*.sql; do
  echo "Running: $migration"
  mysql -u $DB_USER $DB_NAME < "$migration"
  if [ $? -eq 0 ]; then
    echo "✓ $migration completed"
  else
    echo "✗ $migration failed"
    exit 1
  fi
done

echo "All migrations completed successfully!"
