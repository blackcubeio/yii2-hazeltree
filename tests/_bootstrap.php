<?php
/**
 * _bootstrap.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Codeception\Configuration;

date_default_timezone_set('Europe/Paris');

// fcgi doesn't have STDIN and STDOUT defined by default
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));

// init autoloaders
require dirname(__DIR__) . '/vendor/autoload.php';

// Define test environment
defined('YII_ENV') or define('YII_ENV', 'test');
defined('YII_MAINTENANCE') or define('YII_MAINTENANCE', false);
defined('YII_DEBUG') or define('YII_DEBUG', true);

ini_set('display_errors', '1');
error_reporting(E_ALL);

defined('YII_TEST_ENTRY_URL') or define('YII_TEST_ENTRY_URL', parse_url(Configuration::config()['config']['test_entry_url'], PHP_URL_PATH));
defined('YII_TEST_ENTRY_FILE') or define('YII_TEST_ENTRY_FILE', dirname(__DIR__) . '/index.php');

$_SERVER['SCRIPT_FILENAME'] = YII_TEST_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = YII_TEST_ENTRY_URL;
$_SERVER['SERVER_NAME'] = parse_url(Configuration::config()['config']['test_entry_url'], PHP_URL_HOST);
$_SERVER['SERVER_PORT'] = parse_url(Configuration::config()['config']['test_entry_url'], PHP_URL_PORT) ?: '80';

require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';
