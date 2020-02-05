ARG APACHE_VERSION=""
FROM httpd:${APACHE_VERSION:+${APACHE_VERSION}-}alpine

RUN apk update; \
    apk upgrade;

# Copy apache vhost file to proxy php requests to php-fpm container
COPY demo.apache.conf /usr/local/apache2/conf/demo.apache.conf
RUN echo "Include /usr/local/apache2/conf/demo.apache.conf" \
    >> /usr/local/apache2/conf/httpd.conf

FROM php:apache
RUN a2enmod rewrite \
&& docker-php-source extract \
&& docker-php-ext-install mysqli pdo pdo_mysql \
&& echo "date.timezone = Europe/London" > /usr/local/etc/php/conf.d/timezone.ini \
&& docker-php-source delete