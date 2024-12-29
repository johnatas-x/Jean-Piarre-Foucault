#!/bin/bash

# Abort if anything fails
set -e

# Update vendor.
docker exec -it jean-piarre-foucault_php composer install --no-progress --prefer-dist --optimize-autoloader

# Fix perm.
source scripts/fixperm.sh

# Update settings.php.
sudo chmod -R 775 "web/sites/default"
cp ".docker/files/settings.php.default" "web/sites/default/settings.php"
sudo chmod 664 "web/sites/default/settings.php"

# Deploy.
make drush deploy

# Update translations.
make drush locale-check
make drush locale-update

# Rebuild caches.
make drush cr
