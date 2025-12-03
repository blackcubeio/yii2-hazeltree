<?php
/**
 * main.php
 *
 * PHP version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use yii\web\AssetManager;

$config = require 'common.php';

$config['id'] = 'hazeltree-test';
$config['name'] = 'HazelTree Test Application';

$config['components']['request'] = [
    'cookieValidationKey' => 'test-cookie-key-for-hazeltree',
];

$config['components']['assetManager'] = [
    'class' => AssetManager::class,
    'linkAssets' => true,
];

return $config;
