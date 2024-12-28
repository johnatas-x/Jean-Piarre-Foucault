#!/usr/bin/env bash

# Abort if anything fails
set -e

# Create .env if not exists.
if [[ ! -f ".env" ]]; then
  cp ".env.example" ".env"
fi

# Reset project.
make prune
sudo rm -Rf vendor
sudo rm -Rf web/modules/contrib
sudo rm -Rf web/themes/contrib
docker compose up --build -d

# Fix potential Git issue.
docker exec -it jean-piarre-foucault_php sudo chmod o+w /var/www/html
docker exec -it jean-piarre-foucault_php git config --global --add safe.directory /var/www/html

# Install vendor.
docker exec -it jean-piarre-foucault_php composer install --no-progress --prefer-dist --optimize-autoloader

# Copy settings.php.
sudo chmod -R 775 "web/sites/default"
cp ".docker/files/settings.php.default" "web/sites/default/settings.php"
sudo chmod 664 "web/sites/default/settings.php"

# Install site.
make drush "site-install 'minimal' --config-dir=../config/sync --account-name='admin' --account-pass='admin' --yes"
make drush "deploy"
make drush "cache-rebuild"

# Fill data.
make drush "fill-lotto-draws-data --all"

# Fill stats.
make drush "fill-lotto-stats"
