FROM php:7.2-apache

WORKDIR /app
RUN apt-get update && apt-get install -y wget zip unzip git
RUN useradd -ms /bin/bash mum
COPY . /app
RUN chown -R mum:mum /app
RUN chmod +x /app/docker/install_composer.sh && /app/docker/install_composer.sh
USER mum
RUN composer install
ENV APACHE_DOCUMENT_ROOT /app/public
EXPOSE 80