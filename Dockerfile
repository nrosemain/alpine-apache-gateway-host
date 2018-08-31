FROM alpine:latest

MAINTAINER Rosemain Nicolas <nicolas.rosemain@gmail.com>


#propre
RUN apk update && apk upgrade

#installation
RUN apk add \
    apache2 \
    apache2-utils \
    php5-apache2

#creer document
RUN mkdir -p /run/apache2 && \
    mkdir -p /run/script && \
    mkdir -p /web/www && \
    mkdir -p /var/www/localhost/web-config && \
    rm -Rf /var/www/localhost/htdocs && \
    touch /etc/apache2/conf.d/web-config.conf

#configuration
RUN sed -i 's/^#ServerName .*/ServerName localhost/g' /etc/apache2/httpd.conf && \
    sed -i 's/AllowOverride [Nn]one/AllowOverride All/g' /etc/apache2/httpd.conf && \
    sed -i 's/Require ip 127/Require ip 192\n    Require ip 127 /g' /etc/apache2/conf.d/info.conf && \
    sed -i 's/\/var\/www\/localhost\/htdocs/\/web\/www/g' /etc/apache2/httpd.conf && \
    sed -i 's/^#LoadModule rewrite_module modules\/mod_rewrite.so/LoadModule rewrite_module modules\/mod_rewrite.so/g' /etc/apache2/httpd.conf && \
    echo -e 'Alias "/web-config" "/var/www/localhost/web-config" \n' \
            '<Directory "/var/www/localhost/web-config"> \n' \
            '    AllowOverride All \n' \
            '    Options None \n' \
            '    Require ip 192 \n' \
            '    Require ip 127 \n' \
            '</Directory>' > /etc/apache2/conf.d/web-config.conf

#Ajout des fichiers
COPY file/index.php var/www/localhost/web-config/index.php
COPY file/command_php.sh \
     file/command_php.txt \
     file/start.sh \
     run/script/

#permission
RUN chgrp -R www-data /web /run/script /var/www/localhost/web-config && \
    chmod -R 2775 /web /run/script /var/www/localhost/web-config

#finalisation
RUN rm -rf /var/cache/apk/*

EXPOSE 80

CMD ["/bin/sh", "/run/script/start.sh"]

#    echo 'IncludeOptional /web/conf.d/*.conf' >> /etc/apache2/httpd.conf/