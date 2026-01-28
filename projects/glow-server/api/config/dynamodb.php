<?php

return [
    'tables' => [
        'jump_plus_reward' => [
            // key: APP_ENV, value: テーブル名
            'develop' => 'dyn-develop-jump-plus-rewards',
            'dev_ld' => 'dyn-dev-ld-jump-plus-rewards',
            'dev_qa' => 'dyn-dev-qa-jump-plus-rewards',
            'dev_qa2' => 'dyn-dev-qa2-jump-plus-rewards',
            'qa' => 'dyn-qa-jump-plus-rewards',
            'staging' => 'dyn-staging-jump-plus-rewards',
            'review' => 'dyn-review-jump-plus-rewards',
            'production' => 'dyn-prod-jump-plus-rewards',
            'prod' => 'dyn-prod-jump-plus-rewards', # インフラ上ではprodを使っているため、念のため両方登録しておく
        ],
    ],
];
