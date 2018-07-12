FROM php:7.2.7-cli

RUN docker-php-ext-install bcmath
COPY . /
CMD php /worker.php