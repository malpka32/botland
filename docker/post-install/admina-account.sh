#!/bin/sh
set -e

ADMIN_PASS_HASH=$(php bin/console security:hash-password --no-interaction "${ADMIN_PASSWD}" 2>/dev/null | awk '/Password hash/ {for(i=1;i<=NF;i++) if($i ~ /^\$2/) {print $i; exit}}')


mysql -h localhost -h${DB_SERVER=db} -uroot -proot ${DB_NAME} -e "UPDATE ${DB_PREFIX}employee SET firstname='${ADMIN_FIRSTNAME}', lastname='${ADMIN_LASTNAME}', email='${ADMIN_MAIL}', passwd='${ADMIN_PASS_HASH}' WHERE id_employee=1;"

echo "Admin account updated"
echo "Login: ${ADMIN_MAIL}"
echo "Password: ${ADMIN_PASSWD}"
echo "Sign in at: ${PS_DOMAIN}/${PS_FOLDER_ADMIN}"