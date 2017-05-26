#!/bin/sh

set -e

if [ "$1" = "php-fpm" ]; then
    echo "date.timezone = ${DATE_TIMEZONE}" > ${PHP_INI_DIR}/conf.d/date.ini
    echo "short_open_tag = off" > ${PHP_INI_DIR}/conf.d/short_open_tag.ini
    sed -i "s/{gid}/$PHP_GID/g" /usr/local/etc/php-fpm.d/www.conf
    sed -i "s/{uid}/$PHP_UID/g" /usr/local/etc/php-fpm.d/www.conf

    php-fpm
fi
