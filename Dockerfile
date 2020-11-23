FROM ubuntu:20.04
LABEL maintainer "Van Egmond Groep <kenniscentrum@vanegmond.nl>"

ENV PHP_VERSION=74
ENV PATH="/opt/vanegmond/bin:${PATH}"

ENV APP_PATH=/opt/vanegmond/app
ENV APP_PATH_PUBLIC=/opt/vanegmond/app/public
ENV APP_PATH_LOGS=/opt/vanegmond/app/logs
ENV APP_PATH_AUTH=/opt/vanegmond/app/.auth
ENV COMPOSER_HOME=/opt/vanegmond/app/.composer
ENV APP_PATH_DEPLOY=/opt/vanegmond/app/deploy
ENV APP_PATH_DEPLOY_DATA=/opt/vanegmond/app/deploy/.data

# ----- Build Files ----- #

COPY build /

# ----- Packages ----- #

RUN install-packages sudo software-properties-common supervisor curl wget gpg-agent unzip mysql-client git nano restic

# ----- Openlitespeed & PHP ----- #

RUN wget -O - http://rpms.litespeedtech.com/debian/enable_lst_debian_repo.sh | bash

RUN install-packages \
    lsphp$PHP_VERSION \
    lsphp$PHP_VERSION-mysql \
    lsphp$PHP_VERSION-imap \
    lsphp$PHP_VERSION-curl \
    lsphp$PHP_VERSION-common \
    lsphp$PHP_VERSION-json \
    lsphp$PHP_VERSION-redis \
    lsphp$PHP_VERSION-opcache \
    lsphp$PHP_VERSION-igbinary \
    lsphp$PHP_VERSION-imagick \
    lsphp$PHP_VERSION-intl \
    openlitespeed

RUN ln -sf /usr/local/lsws/lsphp$PHP_VERSION/bin/php /usr/local/bin/php \
    && ln -sf /opt/vanegmond/etc/litespeed/httpd_config.conf /usr/local/lsws/conf/httpd_config.conf \
    && ln -sf /opt/vanegmond/etc/php/php.ini /usr/local/lsws/lsphp74/etc/php/7.4/mods-available/99-vanegmond.ini

# ----- Composer ----- #

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && mv composer.phar /usr/local/bin/composer \
    && php -r "unlink('composer-setup.php');" \
    && composer --version

# ----- Runtime ----- #

RUN wget https://github.com/sitepilot/runtime/releases/latest/download/runtime -O /opt/vanegmond/bin/runtime \
    && chmod +x /opt/vanegmond/bin/runtime \
    && runtime --version

# ----- NodeJS ----- #

RUN curl -sL https://deb.nodesource.com/setup_12.x | sudo bash - \
    && install-packages nodejs \
    && npm -v \
    && node -v \
    && npm install -g yarn

# ------ User ----- #

RUN echo "www-data ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers \
    && usermod -u 10000 -d /opt/vanegmond/app www-data \
    && groupmod -g 10000 www-data \
    && chsh -s /bin/bash www-data
    
# ----- Files ----- #

COPY filesystem /

RUN mkdir -p /var/run \
    && mkdir -p /opt/vanegmond/etc \
    && chown -R www-data:www-data /run \
    && chown -R www-data:www-data /opt/vanegmond \
    && chown -R www-data:www-data /usr/local/lsws \
    && ln -sf /dev/stderr /usr/local/lsws/logs/error.log \
    && ln -sf /dev/stderr /usr/local/lsws/logs/stderr.log

# ----- Config ----- #

EXPOSE 8080
EXPOSE 8443

USER 10000:10000

WORKDIR /opt/vanegmond/app

ENTRYPOINT ["/opt/vanegmond/bin/entrypoint"]

CMD ["supervisord", "-c", "/opt/vanegmond/etc/supervisor/supervisor.conf"]
