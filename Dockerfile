FROM php:8.1-cli

WORKDIR /

COPY . /

RUN apt-get update && \
    apt-get install -y \
        git \
        zip \
        unzip && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install

RUN php artisan key:generate

EXPOSE 8000

CMD ["php", "artisan", "process-commissions", "test.csv"]
