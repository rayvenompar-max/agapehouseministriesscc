<?php
/**
 * Application-wide configuration constants.
 */
declare(strict_types=1);

define('APP_NAME',    'Agape House Ministries');
define('APP_LOCATION', 'San Carlos');
define('APP_ENV',     'development');   // 'production' in prod
define('APP_DEBUG',   APP_ENV === 'development');
define('APP_TIMEZONE','Asia/Manila');

date_default_timezone_set(APP_TIMEZONE);

// Never print raw errors to the browser — they corrupt JSON responses.
// In development, errors go to the PHP error log instead.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('log_errors', '1');
} else {
    error_reporting(0);
    ini_set('log_errors', '0');
}
