<?php
require_once __DIR__ . "/../actions/actions_init.php";

if(!isset($_GET['key']) || $_GET['key'] != $CONFIG['CRON_KEY']) {
    echo "Invalid key!";
    http_response_code(403);
    exit;
}

exec('COMPOSER_HOME=/home/lovemyh/.composer /usr/local/php8.4/bin/php /home/lovemyh/admin/composer.phar update --no-interaction --working-dir=/home/lovemyh/admin >> /home/lovemyh/admin/logs/composer_update.log 2>&1 &');
echo "Composer update triggered!";
?>