FROM php:5-fpm

WORKDIR /srv

RUN rm -fr /srv && mkdir -p /srv

COPY ./php.ini /usr/local/etc/php/php.ini

RUN apt-get update && \
  apt-get install -y libzip-dev git && \
  rm -rf /var/lib/apt/lists/* && \
  docker-php-ext-install zip pdo pdo_mysql

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
  php -r "if (hash_file('SHA384', 'composer-setup.php') === '61069fe8c6436a4468d0371454cf38a812e451a14ab1691543f25a9627b97ff96d8753d92a00654c21e2212a5ae1ff36') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
  php composer-setup.php && \
  php -r "unlink('composer-setup.php');" &&\
  mv composer.phar /usr/local/bin/composer

COPY ./workdir/app/composer.json ./workdir/app/composer.lock /srv/workdir/app/

WORKDIR /srv/workdir/app

RUN composer install

COPY ./ /srv
