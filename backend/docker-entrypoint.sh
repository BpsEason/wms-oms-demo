#!/bin/sh
set -e

# 1. 設置程式碼目錄的權限
chown -R www-data:www-data /var/www/html
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# 2. 檢查並運行資料庫 Migration (如果有需要，但通常在 CI/CD 中手動運行)
# php artisan migrate --force

# 3. 啟動 PHP-FPM
php-fpm -D

# 4. 啟動 Laravel Queue Worker (WMS 流程的關鍵)
echo "Starting Laravel Queue Worker..."
php artisan queue:work redis --tries=3 --timeout=60 &

# 保持容器運行
wait -n

# 退出狀態碼
exit $?
