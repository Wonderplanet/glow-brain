# Laravel Wonderplanet Framework マスターアセット管理基盤ライブラリ

api、admin側でマスターデータ、アセットデータを扱うのに必要な処理をまとめたライブラリです

## 概要

マスターデータ、アセットデータ取得に関連する処理を行います。

## 使用方法
### 導入方法

本ライブラリをプロジェクトで使用する場合、composerを使用します

#### 読み込める場所に配置
ディレクトリをプロダクトから読み込める場所に配置します
例として、 `/var/local/wonderplanet/lib/laravel-wp-master-asset-release` に配置するとします。

#### composer.jsonの記載
composer.jsonに取り込む設定を行います。

composer install によってvendor/wonder-planet/ 以下にコピーまたはシンボリックリンクが作成されますので、それを読み込むようにします。
```
    "repositories": [
        {
            "type": "path",
            "url": "/var/local/wonderplanet/lib/laravel-wp-master-asset-release",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        ...
        "wonder-planet/laravel-wp-master-asset-release": "dev-main"
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
    <file>../local/wonderplanet/lib/laravel-wp-master-asset-release/src</file>
```

#### phpstanの設定

phpstan.neonのparameters.pathsに追加します。
```
parameters:
    paths:
        - ../local/wonderplanet/lib/laravel-wp-master-asset-release/src
```

#### deptracの設定
deptrac.yamlからyamlを読み込みます。
```
deptrac:
  paths:
    - ../local/wonderplanet/lib/laravel-wp-master-asset-release/src

  
imports:
  - ../local/wonderplanet/lib/laravel-wp-master-asset-release/deptrac.yaml
```

あとは必要に応じて、layersなどを修正していきます。


#### ユニットテストの設定

ユニットテストを実行する場合、phpunit.xmlのtestsuitesに次の記載を追加します。

主にAPI側で実行することになると思います。

```
        <!-- 共通ライブラリテスト -->
        <testsuite name="Common">
            <directory suffix="Test.php">./vendor/wonder-planet/laravel-wp-master-asset-release/tests/</directory>
        </testsuite>
```

#### ユニットテストについて

ライブラリ導入後、プロダクトのユニットテストを実行すると下記エラーが発生する可能性があります。  
`not found mng_master_release_versions`

これは、ライブラリを導入することで、マスター管理データ内に配信中のデータがない場合に発生します。  
これを回避するには、ユニットテスト実行時に常に下記データを投入するようにしてください。

mng_master_releases

| id | release_key | enabled | target_release_version_id | description |
|----|-------------|---------|---------------------------|-----------|
| 1  | 0 | 1 | 100 | 空もしくはnull |

mng_master_release_versions

| id  | release_key | git_revision   | master_schema_version | data_hash | server_db_hash | client_mst_data_hash | client_mst_data_i18n_ja_hash | client_mst_data_i18n_en_hash | client_mst_data_i18n_zh_hash | client_opr_data_hash | client_opr_data_i18n_ja_hash | client_opr_data_i18n_en_hash | client_opr_data_i18n_zh_hash |
|-----|-------------|----------------|---------------------------|-----------|---|---|---|---|---|---|---|---|---|
| 100 | 0 | ※他のカラムは適当な値でOK |


もし、`laravel-wp-common` ライブラリのImportCsv機能を利用している場合は、下記のように対応してください
- `tests/fixtures/default/api' にcsvデータを配置する
