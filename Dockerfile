FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libzip-dev \
    libxml2-dev \
    wget \
    tar \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install mysqli intl zip soap exif

RUN echo "max_input_vars = 5000" >> /usr/local/etc/php/conf.d/moodle.ini
RUN echo "post_max_size = 120M" >> /usr/local/etc/php/conf.d/moodle.ini
RUN echo "upload_max_filesize = 120M" >> /usr/local/etc/php/conf.d/moodle.ini

RUN wget -q https://github.com/moodle/moodle/archive/refs/tags/v4.4.0.tar.gz -O /tmp/moodle.tar.gz \
    && tar -zxvf /tmp/moodle.tar.gz -C /var/www/html --strip-components=1 \
    && rm /tmp/moodle.tar.gz

RUN mkdir -p /var/www/moodledata

RUN chown -R www-data:www-data /var/www/html /var/www/moodledata \
    && chmod -R 755 /var/www/html /var/www/moodledata
