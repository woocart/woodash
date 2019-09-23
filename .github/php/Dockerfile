FROM php:cli-alpine3.9

LABEL maintainer="jt@niteo.co"
LABEL "com.github.actions.color"="red"
LABEL "com.github.actions.icon"="activity"
LABEL "com.github.actions.name"="PHP"
LABEL "com.github.actions.description"="Base PHP"

RUN apk update && apk add --no-cache make bash composer zip libpng libpng-dev $PHPIZE_DEPS \
    && pecl install xdebug-beta
RUN docker-php-ext-enable xdebug
RUN docker-php-ext-install gd
RUN docker-php-ext-install opcache

SHELL ["/bin/bash", "-c"]
CMD ["."]
