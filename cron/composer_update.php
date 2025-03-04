<?php
exec('COMPOSER_HOME=/home/lovemyh/.composer /usr/local/php8.4/bin/php /home/lovemyh/admin/composer.phar update --no-interaction --working-dir=/home/lovemyh/admin >> /home/lovemyh/admin/logs/composer_update.log 2>&1 &');
echo "Composer update triggered!";
?>