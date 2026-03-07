# Laravel Wonderplanet Framework 計装ライブラリ

現在は次のAPMに対応しています。
- [Datadog](https://www.datadoghq.com/ja/)

## 必要要件

このライブラリはDatadog APMを使用します。Datadog APMは別途インストールを行なってください。

[Tracing PHP Applications](https://docs.datadoghq.com/ja/tracing/trace_collection/automatic_instrumentation/dd_libraries/php/)(Datadog)

インストールはDockerfileなどで行います。

## 概要

Datadogに対応するための機能をまとめています。

### 自動計装の用意

本ライブラリをrequireすることで、laravel-wp-frameworkでのDatadog向けデフォルト計装が有効となります。

## 使用方法
### 導入方法
本ライブラリをプロジェクトで使用する場合、composerを使用します

#### 読み込める場所に配置
ディレクトリをプロダクトから読み込める場所に配置します
例として、 `/var/local/lib/laravel-wp-instrumentation` に配置するとします。

#### composer.jsonの記載
composer.jsonに取り込む設定を行います。

composer install によってvendor/wonder-planet/ 以下にコピーまたはシンボリックリンクが作成されますので、それを読み込むようにします。
```
    "repositories": [
        {
            "type": "path",
            "url": "/var/local/lib/laravel-wp-instrumentation",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        ...
        "wonder-planet/laravel-wp-instrumentation": "dev-main"
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

※すでに既存のlockファイルがある場合は、 `composer update` または `composer update wonder-planet/laravel-wp-instrumentation`を行なってください。

#### phpcs/phpcbfの設定

phpcs.xmlのruleset以下に追加します。
```
    <file>../local/wonderplanet/lib/laravel-wp-instrumentation/src</file>
```

#### phpstanの設定

phpstan.neonのparameters.pathsに追加します。
```
parameters:
    paths:
        - ../local/wonderplanet/lib/laravel-wp-instrumentation/src
```

#### deptracの設定
deptrac.yamlからyamlを読み込みます。
```
deptrac:
  paths:
    - ../local/wonderplanet/lib/laravel-wp-instrumentation/src

  
imports:
  - ../local/wonderplanet/lib/laravel-wp-instrumentation/deptrac.yaml
```

あとは必要に応じて、layersなどを修正していきます。


#### ユニットテストの設定

ユニットテストを実行する場合、phpunit.xmlのtestsuitesに次の記載を追加します。

主にAPI側で実行することになると思います。

```
        <!-- 共通ライブラリテスト -->
        <testsuite name="Common">
            <directory suffix="Test.php">./vendor/wonder-planet/laravel-wp-instrumentation/tests/</directory>
        </testsuite>
```

### 設定

#### 全体の設定

wp_instrumentation.phpファイルにまとめています。そちらを確認してください。

#### フィルタの設定

DatadogInstrumentationに定義しています。

変更する場合はこのクラスを継承してconstなどを調整したクラスをwp_instrumentation.datadog.instrumentation に設定してください。
