# Laravel Wonderplanet Framework マスターアセット管理ツール基盤ライブラリ

admin側でマスターアセットリリース管理ツールを扱うのに必要な処理をまとめたライブラリです

## 概要

マスターアセットリリース管理ツールに関連する処理を行います。

## 使用方法
### 導入方法

本ライブラリをプロジェクトで使用する場合、composerを使用します

#### 読み込める場所に配置
ディレクトリをプロダクトから読み込める場所に配置します
例として、 `/var/local/wonderplanet/lib/laravel-wp-master-asset-release-admin` に配置するとします。

#### composer.jsonの記載
composer.jsonに取り込む設定を行います。

composer install によってvendor/wonder-planet/ 以下にコピーまたはシンボリックリンクが作成されますので、それを読み込むようにします。
```
    "repositories": [
        {
            "type": "path",
            "url": "/var/local/wonderplanet/lib/laravel-wp-master-asset-release-admin",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        ...
        "wonder-planet/laravel-wp-master-asset-release-admin": "dev-main"
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

##### Filamentコードの自動生成について

composer installを実行すると、`laravel-wp-master-asset-release-admin/copy_file` に配置したマスター・アセット管理ツールのコードが自動生成します。  
(すでにコードが存在する場合はスキップします)

ダッシュボード表示用のウィジェットコンポーネントについては手動で設定が必要となります。  
下記のように修正してください。
```php:app/Filament/Pages/Dashboard.php
    public function getWidgets(): array
    {
        return array_merge(
            parent::getWidgets(),
            [
                MngMasterReleaseStatusOverview::class,
            ]
        );
    }
```


#### phpcs/phpcbfの設定

※admin側で使用していない場合はスキップしてください

phpcs.xmlのruleset以下に追加します。
```
    <file>../local/wonderplanet/lib/laravel-wp-master-asset-release-admin/src</file>
```

#### phpstanの設定

※admin側で使用していない場合はスキップしてください

phpstan.neonのparameters.pathsに追加します。
```
parameters:
    paths:
        - ../local/wonderplanet/lib/laravel-wp-master-asset-release-admin/src
```

#### deptracの設定

※admin側で使用していない場合はスキップしてください

deptrac.yamlからyamlを読み込みます。
```
deptrac:
  paths:
    - ../local/wonderplanet/lib/laravel-wp-master-asset-release-admin/src

  
imports:
  - ../local/wonderplanet/lib/laravel-wp-master-asset-release-admin/deptrac.yaml
```

あとは必要に応じて、layersなどを修正していきます。


#### ユニットテストの設定

ユニットテストを実行する場合、phpunit.xmlのtestsuitesに次の記載を追加します。

主にadmin側で実行することになると思います。

```
        <!-- 共通ライブラリテスト -->
        <testsuite name="Common">
            <directory suffix="Test.php">./vendor/wonder-planet/laravel-wp-master-asset-release-admin/tests/</directory>
        </testsuite>
```

#### .envファイルの設定
admin/.envには下記を設定してください
```
# マスターデータのdumpファイル配置場所を指定(環境間インポート用)
AWS_MASTER_DATA_MYSQLDUMP_BUCKET=

# マスターデータgitリポジトリ
GIT_REPOSITORY=

# マスターデータ取り込み操作対象のブランチ
GIT_BRANCH=

# 環境で使用するマスターデータのスプレッドシートID
SHEET_DIR_ID=

# アセットとマスタのデータが取り込み可能な環境の一覧
IMPORTABLE_FROM_ENVIRONMENT_LIST=
例
IMPORTABLE_FROM_ENVIRONMENT_LIST=develop,develop1,develop2,develop3

# アセット配信管理ダッシュボード用JenkinsURL
IOS_ASSET_CREATE_JENKINS_URL=
ANDROID_ASSET_CREATE_JENKINS_URL=
```

#### configの設定
ライブラリに必要なconfigの設定は `config/wp_master_asset_release_admin.php` に記載されています。
必要に応じて適宜調整してください。
ファイルはcomposer install 実行時に生成されます。

#### storage以下の設定

- `storage/app/env/` に、スプレッドシートにアクセスするため `seed-spread-sheet-access.json` 配置してください
- `storage/app/` 以下に下記ディレクトリを用意してください
  - `database_csv` -> データベースに取り込める形式のCSVファイルのディレクトリ
  - `client_json` -> クライアント用にシリアライズしたJSONファイルのディレクトリ
  - `masterdata_csv/masterdata_validation/csv` -> バリデーション用のCSVファイルのディレクトリ 
  - `masterdata_validation` -> 同上
  - `masterdata_mysqldump` -> 環境間インポート用に生成した対象リリースバージョンDBのdumpファイル用ディレクトリ
  - `download_masterdata_mysqldump` -> 環境間インポート用に生成した対象リリースバージョンDBのdumpファイル用ディレクトリ

#### ユニットテストについて

ライブラリ導入後、プロダクトのadmin側のユニットテストを実行すると下記エラーが発生する可能性があります。  
`not found released master data`

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
- factoryクラスを用意する
