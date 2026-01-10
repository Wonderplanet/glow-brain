# Laravel Wonderplanet Framework 共通機能ライブラリ

app/admin/lib以下で共通で使用するためのコードを扱うライブラリです。

## 概要

共通で使用するためのコードをまとめています。

ユニットテストでも共通化が行えるよう、こちらも参照できるようにしています。

## 使用方法
### 導入方法
本ライブラリをプロジェクトで使用する場合、composerを使用します

#### 読み込める場所に配置
ディレクトリをプロダクトから読み込める場所に配置します
例として、 `/var/local/lib/laravel-wp-common` に配置するとします。

#### composer.jsonの記載
composer.jsonに取り込む設定を行います。

composer install によってvendor/wonder-planet/ 以下にコピーまたはシンボリックリンクが作成されますので、それを読み込むようにします。
```
    "repositories": [
        {
            "type": "path",
            "url": "/var/local/lib/laravel-wp-common",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        ...
        "wonder-planet/laravel-wp-common": "dev-main"
    },
    "autoload": {
        "psr-4": {
            ...
            "Wonderplanet\\": "vendor/wonder-planet/"
        }
```

#### composer installの実行
composer installを行うと、必要なファイルがvendor以下にコピーまたはシンボリックリンクが作成されます。
```
composer install
```

#### phpcs/phpcbfの設定

phpcs.xmlのruleset以下に追加します。
```
    <file>../local/wonderplanet/lib/laravel-wp-common/src</file>
```

#### phpstanの設定

phpstan.neonのparameters.pathsに追加します。
```
parameters:
    paths:
        - ../local/wonderplanet/lib/laravel-wp-common/src
```

#### deptracの設定
deptrac.yamlからyamlを読み込みます。
```
deptrac:
  paths:
    - ../local/wonderplanet/lib/laravel-wp-common/src

  
imports:
  - ../local/wonderplanet/lib/laravel-wp-common/deptrac.yaml
```

あとは必要に応じて、layersなどを修正していきます。


#### ユニットテストの設定

ユニットテストを実行する場合、phpunit.xmlのtestsuitesに次の記載を追加します。

主にAPI側で実行することになると思います。

```
        <!-- 共通ライブラリテスト -->
        <testsuite name="Common">
            <directory suffix="Test.php">./vendor/wonder-planet/laravel-wp-common/tests/</directory>
        </testsuite>
```

### 設定

以下の値を環境変数または.envに記載します。
(AWS SecretManagerやParameterStoreを使用する場合は、環境変数からの読み込みとなります)

wp_common.phpファイルで、対応するキーを読み込んでいる設定を編集しても反映されます。

#### DBコネクション

値|内容|デフォルト
-- | -- | --
DB_CONNECTION_MST | mst/opr DBを参照するコネクション名 | mysql
DB_CONNECTION_USR | usr DBを参照するコネクション名 | mysql
DB_CONNECTION_LOG | log DBを参照するコネクション名 | mysql
DB_CONNECTION_ADMIN | admin DBを参照するコネクション名 | admin

#### リクエストのユニークID

値|内容|デフォルト
-- | -- | --
REQUEST_UNIQUE_ID_HEADER_KEY | リクエストのユニークIDが設定されるヘッダキー | Unique-Request-Identifier
