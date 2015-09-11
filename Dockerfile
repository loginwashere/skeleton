# PHP FPM image
FROM phusion/baseimage:0.9.17

MAINTAINER Dmitriy Savchenko <login.was.here@gmail.com>

ENV PHP_VERSION 5.6.13*
ENV PHP_XDEBUG_VERSION 2.2.3
ENV MYSQL_CLIENT_MAJOR_VERSION 5.6

ENV HOME /root
ENV DEBIAN_FRONTEND noninteractive
ENV INITRD No

# Add alternative php packages repository
RUN apt-get install -y software-properties-common
RUN echo "deb http://ppa.launchpad.net/ondrej/php5-5.6/ubuntu trusty main" > /etc/apt/sources.list.d/ondrej-php5-5_6-trusty.list && apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C

# Update source list.
RUN apt-get update && \
    apt-get upgrade -y -o Dpkg::Options::="--force-confold" && \
    apt-get install -y --force-yes \
    git \
    curl \
    php5-fpm=${PHP_VERSION} \
    php5-cli=${PHP_VERSION} \
    php5-dev=${PHP_VERSION} \
    php5-curl=${PHP_VERSION} \
    php5-mysql=${PHP_VERSION} \
    php-pear=${PHP_VERSION} \
    php5-mcrypt=${PHP_VERSION} \
    php5-gd=${PHP_VERSION} \
    mysql-client-${MYSQL_CLIENT_MAJOR_VERSION}

# Install XDebug
RUN pecl install xdebug-${PHP_XDEBUG_VERSION}
COPY ./docker/php-5.6.13/xdebug.ini /etc/php5/mods-available/xdebug.ini
RUN php5enmod xdebug

# Install composer
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    composer self-update && \
    apt-get clean

# Cleanup APT.
RUN rm -rf /var/lib/apt/lists/*

# Configure FPM WWW pool
RUN sed -i 's/;daemonize = yes/daemonize = no/' /etc/php5/fpm/php-fpm.conf
COPY ./docker/php-5.6.13/www.conf /etc/php5/fpm/pool.d/www.conf

# Forward error logs to docker log collector
RUN ln -sf /dev/stderr /var/log/fpm-php.www.log

WORKDIR /var/www/app

ADD . /var/www/app/

RUN composer install --prefer-dist

ADD ./docker/php-5.6.13/db.php /var/www/app/application/configs/dev/

# Expose FPM port.
EXPOSE 9000

# Define default command.
CMD ["php5-fpm"]
