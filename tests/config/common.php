<?php
/**
 * common.php
 *
 * PHP version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use yii\db\Connection;
use yii\log\FileTarget;

require_once __DIR__ . '/db.php';

Yii::setAlias('@blackcube/hazeltree', dirname(__DIR__, 2) . '/src');
Yii::setAlias('@tests', dirname(__DIR__));

$config = [
    'sourceLanguage' => 'en',
    'language' => 'en-US',
    'basePath' => dirname(__DIR__),
    'vendorPath' => dirname(__DIR__, 2) . '/vendor',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'components' => [
        'db' => [
            'class' => Connection::class,
            'dsn' => 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASS,
            'charset' => 'utf8mb4',
        ],
        'cache' => [
            'class' => yii\caching\DummyCache::class,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
    'params' => [],
];

return $config;
