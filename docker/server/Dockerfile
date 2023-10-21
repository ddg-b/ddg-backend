FROM php:8.2-apache

ENV LINUX_USER=dockeruser

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y git unzip zip nodejs zsh vim sudo wget redis-tools npm

WORKDIR /var/www/html

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions gd pdo_mysql bcmath zip intl opcache redis

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN echo "Install XDEBUG" && \
        pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.mode=debug,coverage,develop" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_port=9005" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    pecl clear-cache

RUN npm install -g @angular/cli

# creates the user with uid/guid = host's uid/guid
RUN groupadd --gid 1000 ${LINUX_USER} && useradd -ms /bin/zsh --uid 1000 --gid 1000 --password "$(openssl passwd -6 ${LINUX_USER})" ${LINUX_USER}
RUN usermod -a -G sudo ${LINUX_USER}
RUN mkdir /home/${LINUX_USER}/.ssh && chmod 0700 /home/${LINUX_USER}/.ssh

RUN runuser -l ${LINUX_USER} -c 'sh -c "$(curl -fsSL https://raw.githubusercontent.com/coreycole/oh-my-zsh/master/tools/install.sh)"'
RUN chsh -s $(which zsh) ${LINUX_USER}

RUN usermod -G 1000 www-data
RUN chown -R ${LINUX_USER}:${LINUX_USER} /var/www/html

RUN runuser -l ${LINUX_USER} -c 'composer global require friendsofphp/php-cs-fixer'
RUN runuser -l ${LINUX_USER} -c 'composer global require "squizlabs/php_codesniffer"'
RUN runuser -l ${LINUX_USER} -c 'composer global require phpstan/phpstan'
RUN runuser -l ${LINUX_USER} -c 'composer global require phpstan/phpstan-deprecation-rules'

# .zshrc
COPY ./docker/cli/zshrc /home/${LINUX_USER}/.zshrc
RUN chown -R ${LINUX_USER}:${LINUX_USER} /home/${LINUX_USER}
# docker exec -it --user dockeruser [container-name] zsh