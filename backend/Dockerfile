# 使用 PHP-FPM Alpine 版本作為基礎鏡像
FROM php:8.2-fpm-alpine

# 安裝系統依賴和 PHP 擴展
RUN apk update && apk add \
    nginx \
    git \
    curl \
    libzip-dev \
    libpng-dev \
    postgresql-dev \
    unzip \
    icu-dev \
    && docker-php-ext-install pdo pdo_pgsql zip bcmath gd intl \
    && apk del --no-cache

# 安裝 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 設定工作目錄
WORKDIR /var/www/html

# 設定容器啟動時的腳本 (用於運行佇列 worker 和權限設置)
# 該 entrypoint.sh 必須在程式碼掛載後執行，以確保權限正確
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# 暴露 PHP-FPM 端口
EXPOSE 9000

# 預設執行 PHP-FPM
CMD ["php-fpm"]
