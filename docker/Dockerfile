FROM prestashop/prestashop:9.0.3

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV TZ=Europe/Warsaw

RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates cron tzdata $PHPIZE_DEPS \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && echo "apc.enable_cli=1" > /usr/local/etc/php/conf.d/apcu-cli.ini \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime \
    && echo ${TZ} > /etc/timezone \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY modules/ /opt/modules/
COPY docker/post-install/*.sh /tmp/post-install-scripts/
COPY docker/init/*.sh /tmp/init-scripts/

RUN chmod +x -R /tmp/init-scripts
RUN chmod +x -R /tmp/post-install-scripts
