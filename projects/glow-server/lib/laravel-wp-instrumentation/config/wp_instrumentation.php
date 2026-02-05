<?php

/**
 * DataDogの計装設定
 */

use WonderPlanet\Domain\Instrumentation\Instrumentations\DatadogInstrumentation;

return [
    /**
     * 計装対象のキャッシュについての設定
     */
    'cache' => [
        /**
         * キャッシュを有効にするか
         * 
         * 無効にした場合、対象クラス/メソッドをキャッシュに保存せず、
         * 毎回autoloaderより読み込むようになります
         * 
         * キャッシュはAPCuに保存されるため、コンテナを再起動すれば無効化されます。
         * そのため通常の使用ではデプロイ時にクリアされることが想定されるので、オンにしておきます。
         * 
         * 開発中や計装調整中にオフにすることで、キャッシュの影響を受けずに計装の確認を行うことができます。
         */
        'enable' => env('WP_INSTRUMENTATION_CACHE_ENABLE', true),
    ],

    /**
     * 計装のフィルタ設定
     */
    'filter' => [
        /**
         * 計測対象の名前空間
         * 本当はリスト化したくないけれど、次の理由から対象を絞る
         * - クラス数が多すぎて256MBのメモリを超ること、
         * - is_subclass_ofでエラーになるクラスが多すぎるため対象を絞る
         *   Declaration of 〜で型が合わないエラーや、存在しないファイル/クラスを読み込もうとしてnot foundエラーになる
         * - Illuminateのクラスも含めると、実行時にクラッシュするphp-fpmが発生し、systemd-coredumpぜEC2全体が重くなる
         */
        'enable_namespaces' => [
            'App\\',
            'WonderPlanet\\',
        ],

        /**
         * 計測対象外にする名前空間・クラス名
         * 
         * 正規表現で指定する
         */
        'exclusion_namespaces' => [
            '\\\\.*Factory',
        ],

        /**
         * 計測対象外にするクラス名 (前方一致)
         */
        'exclusion_start_with_namespaces' => [
            // テストの計測は不要
            'Illuminate\\Testing\\',

            // class_existsでエラーになるため、Doctrineは主にマイグレーションで使用するので除外しても問題ない
            // Declaration of Doctrine\Common\Cache\Psr6\CacheItem::get() 
            // must be compatible with Psr\Cache\CacheItemInterface::get()
            'Doctrine\\',

            // telescopeの計測は不要
            'Laravel\\Telescope\\',

            // 取得しても参考にしなさそうなクラスは不要
            // トレース漏れなどで必要になったら個別に測定することを考える
            'Illuminate\\Support\\Env',
            'Illuminate\\Support\\Arr',
            'Illuminate\\Routing\\Route',
            'Illuminate\\Support\\Str',
            'Illuminate\\Routing\\Matching',
            'Illuminate\\Config\\Repository',
            'Illuminate\\Container\\Container',
            'Illuminate\\Foundation\\Application',
            'Illuminate\\Support\\Collection',
            'Illuminate\\Container\\Util',
            'Illuminate\\Foundation\\AliasLoader',
            'Illuminate\\Database\\Query\\Grammars\\',
            'Illuminate\\Events\\',
            'Illuminate\\Database\\Grammar',
            'Illuminate\\Database\\MySqlConnection',
            'Illuminate\\Database\\Connection',
            'Illuminate\\Support\\Pluralizer',
            'Illuminate\\Support\\Carbon',
            'Illuminate\\Database\\DatabaseManager',
            'Illuminate\\Support\\Facades\\Date',
            'Illuminate\\Database\\Eloquent\\Collection',
            'Illuminate\\Routing\\MiddlewareNameResolver',
            'Illuminate\\Database\\Query\\Builder',
            'Illuminate\\Database\\Eloquent\\Builder',
            'Dotenv\\',
            'Brick\\Math\\',
            'Carbon\\',

            // is_subclass_ofでエラーになるため、Fakerのクラスは除外
            // Class "Doctrine\Persistence\Mapping\ClassMetadata" not found 
            // in /var/www/vendor/fakerphp/faker/src/Faker/ORM/Doctrine/backward-compatibility.php:6
            // が発生する
            // Fakerはテスト用のクラスなので、計測から外す
            'Faker\\',

            // Googleのクラスだけで6000以上あるので、除外
            'Google\\',

            // is_subclass_ofでエラーになるクラス群
            // Declaration of 〜で型が合わないエラーが発生する
            'Google\\Auth\\Cache\\Item',
            'Maatwebsite\\',
            'PhpOffice\\',
            'Psy\\',
        ],

        /**
         * 計測から除外するクラス
         * 継承しているクラスも除外する
         */
        'exclusion_classes' => [
            // Eloqunetモデルは除く
            \Illuminate\Database\Eloquent\Model::class,
            // Requestクラスは除く
            \Illuminate\Http\Request::class,
            // Structクラスは除く
            \App\Http\Lib\Structs\Struct::class,
        ],

        /**
         * 計測から除外するメソッド
         */
        'exclusion_methods' => [
            // そのまま文字列比較
            'start_with' => [
                'Illuminate\\Support\\Carbon::getTestNow',
                'Illuminate\\Database\\Query\\Builder::castBinding',
                'Illuminate\\Support\\Facades\\Auth::__callStatic',
                'Illuminate\\Support\\Facades\\Auth::getFacadeRoot',
                'Illuminate\\Auth\\AuthManager::guard',
                'Illuminate\\Support\\Facades\\Facade::getFacadeRoot',
                'Illuminate\\Support\\Facades\\Facade::__callStatic',
                'Illuminate\\Auth\\AuthManager::getDefaultDriver',
                'Illuminate\\Database\\Query\\Builder::toSql',
            ],

            // 正規表現でマッチ
            'regex' => [
                // コンストラクタは除外
                // 全部一致しているのか、何も送られなくなってしまった…
                '\\\\.*::__construct',
                // ただのgetter、setterは除外
                '\\\\.*::get',
                '\\\\.*::set',
            ]
        ]
    ],

    'datadog' => [
        /**
         * DataDogの計装を有効にするか
         */
        'enable' => env('WP_INSTRUMENTATION_DATADOG_ENABLE', false),

        /**
         * 自動計装のメソッド一覧を取得するクラス
         */
        'instrumentation' => DatadogInstrumentation::class,
    ],
];
