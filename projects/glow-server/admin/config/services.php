<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'dynamodb_maintenance' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'table' => env('AWS_DYNAMODB_MAINTENANCE_TABLE'),
    ],

    'eventbridge_start_scheduler_maintenance' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'schedule_name' => env('SYSTEM_MAINTENANCE_SCHEDULER_NAME_START'),
    ],

    'eventbridge_end_scheduler_maintenance' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'schedule_name' => env('SYSTEM_MAINTENANCE_SCHEDULER_NAME_END'),
    ],

    'lambda_maintenance' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'lambda_function_arn' => env('SYSTEM_MAINTENANCE_LAMBDA_FUNCTION_ARN'),
        'function_name' => env('MAINTENANCE_LAMBDA_FUNCTION_NAME'),
    ],

    'slack' => [
        'datalake_webhook_url' => env('DATALAKE_SLACK_WEBHOOK_URL'),
        'webhook_url' => env('SLACK_WEBHOOK_URL'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL') . '/login/google/callback',
    ],

    'lambda_jump_plus_reward' => [
        'region' => env('AWS_DEFAULT_REGION'),
        'function_name' => env('JUMP_PLUS_REWARD_LAMBDA_FUNCTION_NAME'),
    ],

    /**
     * 各環境がどのAWSアカウントに属しているか
     * key: 環境名
     * value: AWSアカウント
     */
    'aws_account' => [
        'develop' => 'dev',
        'dev_ld' => 'dev',
        'dev_qa' => 'dev',
        'dev_qa2' => 'dev',
        'qa' => 'dev',
        'staging' => 'staging',
        'review' => 'prod',
        'production' => 'prod',
    ],

    'athena' => [
        'region' => env('AWS_DEFAULT_REGION'),
        'database' => env('ATHENA_DATABASE', 'glow_develop_user_action_logs'),
        'workgroup' => env('ATHENA_WORKGROUP', 'glow-server-admin'),
        // ECS/EC2ロールを使用するので key, secret は設定しない
    ],
];
