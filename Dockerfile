FROM alpine:latest

MAINTAINER Rosemain Nicolas <nicolas.rosemain@gmail.com>

#propre
RUN apk update && apk upgrade

#installation
RUN apk add \
    apache2 \
    apache2-utils \
    apache2-proxy \
    php5-apache2

#creer document
RUN mkdir -p /run/apache2 && \
    mkdir -p /run/script && \
    mkdir -p /work/vhost && \
    rm /var/www/localhost/htdocs/index.html

#Ajout des fichiers
COPY file/index.php \
     file/style.css \
     /var/www/localhost/htdocs/
COPY file/start.sh /run/script/
COPY file/httpd.conf /etc/apache2/
COPY file/proxy.conf /etc/apache2/conf.d/proxy.conf

#permission
RUN chgrp -R www-data /work  && \
    chmod -R 2775 /work

#finalisation
RUN rm -rf /var/cache/apk/*

EXPOSE 80

CMD ["/bin/sh", "/run/script/start.sh"]
