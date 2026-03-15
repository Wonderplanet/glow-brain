# Laravel Wonderplanet Framework 共通機能ライブラリ

admin/lib以下で共通で使用するためのコードを扱うライブラリです。

## 概要

共通で使用するためのコードをまとめています。

ユニットテストでも共通化が行えるよう、こちらも参照できるようにしています。

## 使用方法
### 導入方法
本ライブラリをプロジェクトで使用する場合、composerを使用します

#### 読み込める場所に配置
ディレクトリをプロダクトから読み込める場所に配置します
例として、 `/var/local/lib/laravel-wp-common-admin` に配置するとします。

#### composer.jsonの記載
composer.jsonに取り込む設定を行います。

composer install によってvendor/wonder-planet/ 以下にコピーまたはシンボリックリンクが作成されますので、それを読み込むようにします。
```
    "repositories": [
        {
            "type": "path",
            "url": "/var/local/lib/laravel-wp-common-admin",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        ...
        "wonder-planet/laravel-wp-common-admin": "dev-main"
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

※admin側で使用していない場合はスキップしてください

phpcs.xmlのruleset以下に追加します。
```
    <file>../local/wonderplanet/lib/laravel-wp-common-admin/src</file>
```

#### phpstanの設定

※admin側で使用していない場合はスキップしてください

phpstan.neonのparameters.pathsに追加します。
```
parameters:
    paths:
        - ../local/wonderplanet/lib/laravel-wp-common-admin/src
```

#### deptracの設定

※admin側で使用していない場合はスキップしてください

deptrac.yamlからyamlを読み込みます。
```
deptrac:
  paths:
    - ../local/wonderplanet/lib/laravel-wp-common-admin/src

  
imports:
  - ../local/wonderplanet/lib/laravel-wp-common-admin/deptrac.yaml
```

あとは必要に応じて、layersなどを修正していきます。


#### ユニットテストの設定

ユニットテストを実行する場合、phpunit.xmlのtestsuitesに次の記載を追加します。

主にAPI側で実行することになると思います。

```
        <!-- 共通ライブラリテスト -->
        <testsuite name="Common">
            <directory suffix="Test.php">./vendor/wonder-planet/laravel-wp-common-admin/tests/</directory>
        </testsuite>
```

#### Filamentのコード自動生成について

admin用のライブラリには、Filamentのクラスを自動生成する公開リソースを用意しています。  
composer.json に組み込むことで、composer install/update時にスクリプトを実行します。

- リソース公開コマンド
  - `php artisan vendor:publish --tag=wp-admin-files --ansi`
- composerで自動実行したい場合
  - ライブラリ側のcomposer.jsonに下記を追記
  ```
    "scripts": {
        ~省略~
        "post-install-cmd": [
            "@php artisan vendor:publish --tag=wp-admin-files --ansi"
        ],
        "post-update-cmd": [
            ~省略~
            "@php artisan vendor:publish --tag=wp-admin-files --ansi"
        ],
        ~省略~
    },
  ```
