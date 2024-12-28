ARG PHP_TAG=8.4-dev-4.65.4
FROM wodby/drupal-php:${PHP_TAG}

USER root

ARG WODBY_USER_ID
ARG WODBY_GROUP_ID

RUN groupmod -g $WODBY_GROUP_ID wodby && \
    usermod -u $WODBY_USER_ID -g wodby wodby && \
    chown -R wodby:wodby /var/www/html

USER wodby
