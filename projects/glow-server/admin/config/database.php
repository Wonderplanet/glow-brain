<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [
        'admin' => [
            'driver' => 'mysql',
            'host' => env('ADMIN_DB_HOST', '127.0.0.1'),
            'port' => env('ADMIN_DB_PORT', '3306'),
            'database' => env('ADMIN_DB_DATABASE', 'admin'),
            'username' => env('ADMIN_DB_USERNAME', '__MISSING_USER_NAME__'),
            'password' => env('ADMIN_DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_bin',//'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
            'timezone'       => '+09:00',
        ],

        'tidb' => [
            'driver'         => 'mysql',
            'host'           => env('TIDB_HOST', '127.0.0.1'),
            'port'           => env('TIDB_PORT', '4000'),
            'database'       => env('TIDB_DATABASE', 'local'),
            'username'       => env('TIDB_USERNAME', '__MISSING_USER_NAME__'),
            'password'       => env('TIDB_PASSWORD', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_unicode_ci',
            'prefix'         => '',
            'prefix_indexes' => true,
            'strict'         => true,
            'engine'         => null,
            'options'        => extension_loaded('pdo_mysql') ? array_filter([
                // TIDBとMySQLで設定を分離したいのでMYSQL_ATTR_SSL_CAとは読み込み先を変える
                PDO::MYSQL_ATTR_SSL_CA => env('TIDB_MYSQL_ATTR_SSL_CA'),
                PDO::ATTR_PERSISTENT => env('TIDB_PERSISTENT', false),
            ]) : [],
            'timezone'       => '+09:00',
        ],

        // マスターデータ管理の都合上mstの接続先のデータベース名が変更になる場合があるので絶対に変わらない接続設定を用意しておく
        'default_mst' => [
            'driver'         => 'mysql',
            'host'           => env('MASTER_DB_HOST', '127.0.0.1'),
            'port'           => env('MASTER_DB_PORT', '3306'),
            'database'       => env('MASTER_DB_DATABASE', '__MISSING_DB_NAME__'),
            'username'       => env('MASTER_DB_USERNAME', '__MISSING_USER_NAME__'),
            'password'       => env('MASTER_DB_PASSWORD', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_bin',//'utf8mb4_unicode_ci',
            'prefix'         => '', // 自動でテーブル名に接頭辞が付いてしまうので空文字にしておく
            'prefix_indexes' => true,
            'strict'         => true,
            'engine'         => null,
            'options'        => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
            'timezone'       => '+09:00',
        ],

        'mst' => [
            'driver'         => 'mysql',
            'host'           => env('MASTER_DB_HOST', '127.0.0.1'),
            'port'           => env('MASTER_DB_PORT', '3306'),
            'database'       => env('MASTER_DB_DATABASE', '__MISSING_DB_NAME__'),
            'username'       => env('MASTER_DB_USERNAME', '__MISSING_USER_NAME__'),
            'password'       => env('MASTER_DB_PASSWORD', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_bin',//'utf8mb4_unicode_ci',
            'prefix'         => '', // 自動でテーブル名に接頭辞が付いてしまうので空文字にしておく
            'prefix_indexes' => true,
            'strict'         => true,
            'engine'         => null,
            'options'        => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
            'timezone'       => '+09:00',
        ],

        'mng' => [
            'driver'         => 'mysql',
            'host'           => env('MANAGE_DB_HOST', '127.0.0.1'),
            'port'           => env('MANAGE_DB_PORT', '3306'),
            'database'       => env('MANAGE_DB_DATABASE', 'mng'),
            'username'       => env('MANAGE_DB_USERNAME', '__MISSING_USER_NAME__'),
            'password'       => env('MANAGE_DB_PASSWORD', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_bin',//'utf8mb4_unicode_ci',
            'prefix'         => '',
            'prefix_indexes' => true,
            'strict'         => true,
            'engine'         => null,
            'options'        => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
            'timezone'       => '+09:00',
        ],

        // レビュー環境マイグレーション用設定
        'review_admin' => [
            'driver' => 'mysql',
            'host' => env('REVIEW_ADMIN_DB_HOST', '__MISSING_HOST__'),
            'port' => '3306',
            'database' => 'admin',
            'username' => env('REVIEW_ADMIN_DB_USERNAME', '__MISSING_USER_NAME__'),
            'password' => env('REVIEW_ADMIN_DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_bin',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
            'timezone'       => '+09:00',
        ],

        // 本番環境マイグレーション用設定
        'prod_admin' => [
            'driver' => 'mysql',
            'host' => env('PROD_ADMIN_DB_HOST', '__MISSING_HOST__'),
            'port' => '3306',
            'database' => 'admin',
            'username' => env('PROD_ADMIN_DB_USERNAME', '__MISSING_USER_NAME__'),
            'password' => env('PROD_ADMIN_DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_bin',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
            'timezone'       => '+09:00',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_ENV', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
