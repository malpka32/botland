#!/bin/sh
set -e

for module in /opt/modules/*; do
  name="${module##*/}"
  echo "Installing module: $name"
  cp -r $module /var/www/html/modules/
  chmod -R 777 /var/www/html/modules/$name
  if [ -f "/var/www/html/modules/$name/composer.json" ]; then
    echo "Generating Composer autoload for module: $name"
    (
      cd "/var/www/html/modules/$name"
      composer dump-autoload --no-interaction --optimize
    )
  fi
  php /var/www/html/bin/console prestashop:module -n install "$name" || true
  echo "Module installed: $name"
done

chown -R www-data:www-data /var/www/html/