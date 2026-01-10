# Laravel Wonderplanet Framework 通貨基盤ライブラリ

課金・通貨基盤において通貨処理で必要になる処理をまとめたライブラリ

## 概要
一次通貨、二次通貨に関連する処理を行います。

通貨の追加や消費、状態の取得などになります。

その他はbilling側で処理します。

## 使用方法
### 導入方法
本ライブラリをプロジェクトで使用する場合、composerを使用します

※本ライブラリはlaravel-wp-commonライブラリを使用しています。

更新を行う場合、laravel-wp-commonライブラリもバージョンを合わせて更新してください。

#### 読み込める場所に配置
ディレクトリをプロダクトから読み込める場所に配置します
例として、 `/var/local/lib/laravel-wp-currency` に配置するとします。

#### composer.jsonの記載
composer.jsonに取り込む設定を行います。

composer install によってvendor/wonder-planet/ 以下にコピーまたはシンボリックリンクが作成されますので、それを読み込むようにします。
```
    "repositories": [
        {
            "type": "path",
            "url": "/var/local/lib/laravel-wp-currency",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        ...
        "wonder-planet/laravel-wp-currency": "dev-main"
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

#### 設定ファイルなどの転送
artisan vendor:publishでファイルをコピーします。

```
php artisan vendor:publish --tag=wp
```
ファイルがすでに存在している場合は転送されません。上書きする場合は--forceをつけます。
```
php artisan vendor:publish --tag=wp --force
```

管理ツール側にも、設定ファイルやマイグレーションファイルをコピーして設定する必要があります。
wp-adminのタグを実行してください。
```
php artisan vendor:publish --tag wp-admin
```

#### 設定ファイルの修正
config/wp_currency.phpを修正します
DB名やテーブル名をここで指定します。

#### マイグレーションの実行
artisan migrate を実行します。
```
php artisan migrate
php artisan admin migrate
```

### コネクションの設定
本ライブラリでは、マスタデータとユーザーデータを垂直分割して扱っています。
そのため次のコネクションが何に当たるかをconfigに設定します。
config/database.phpのconnectionsに、次のコネクションを設定します。

- マスタデータを扱うコネクション
- ユーザーデータを扱うコネクション

#### デフォルトの設定

デフォルトでは次のコネクション名を想定しています。

- マスタ関連テーブルを作成するコネクション ... mysql
- ユーザー関連のテーブルを作成するコネクション ... mysql (フレームワークリポジトリはこれでユーザーデータを扱っていたため)

※ユニットテストのrefreshDatabaseなどがデフォルトのコネクションのみ対応となっているため、現在は同じmysqlコネクションを指定しています

### ストレージの設定

課金ログレポートなど、ストレージ領域にファイルを出力する機能があります。

主に管理ツール側になると思いますが、storageディレクトリにphpプログラムから読み書きを行うことができることを確認してください。

#### 課金ログレポートなどのファイル生成がうまく動作しない場合

storageディレクトリのファイル権限が問題となっている可能性があります。

管理ツールのコンテナに入り、次のことを確認してください。

##### ・storage/app 以下にファイルを作成することができるか
多くは/var/www/storage/app にマウントされていると思います。

`ls -al` を実行して、ディレクトリの所有者や権限が、phpを実行しているユーザーで問題なく書き込めることを確認してください。

phpを実行しているユーザーは、psコマンドで確認するなどしてください。

一部のレポートが作成されない、エラーになる場合はさらに、そのファイルパスに作成権限があることを確認してください。

## 使用方法(外貨為替定期収集ツール)
管理ツール側の機能として、外貨の為替レートを収集、適用するシステムが組み込まれています。

### コマンドの定期実行
為替レートの記載されているサイトから、レートを収集しています。
そのためのコマンドを定期的に実行してください。

実行するコマンドは次のものになります。
```
php artisan app:scrape-foreign-currency-rate-command
```

### 参考
参考issue: [[管理ツール] 集計：外貨為替定期収集ツール実装 #184](https://github.com/Wonderplanet/laravel-wp-framework/issues/184)

## 開発環境と本番環境の判別

動作している環境が開発環境か本番環境かは、wp_currency.is_debuggable_environment_function に設定される関数を呼び出して判定します。
判定方法はプロダクトによって違うと思うので、こちらに判定を記述してください。
簡易的には.envでの設定を用意しています。

## 注意事項

使用するプロダクト側で注意する点を記載しています。

### 時刻・タイムゾーンの扱いについて

各モデルやログのタイムゾーンは、プログラム/サーバーともにUTCを想定しています。


#### データを読み出す場合

Eloquentモデルの生成においては、特に変換を行っていません。

そのためlaravelの仕様により、UTCのCarbonオブジェクトとして生成されます。

課金ログレポートなど日付の照合ロジックがある場合は、レコードの時刻をUTCとして処理を行なっています。

検索条件として渡されたCarbonをutcに変換してから、判定などを行なっています。

#### データを記録する場合

Elquentモデルのcreated_atなどは、Eloquentモデルで設定された値をそのままモデルに設定して保存しています。

そのため結果的にUTCとなっている想定です。

記録する時刻をAPI処理時刻に固定する場合、`Carbon::setTestNow`などを使用して対応してください。

## 必要パッケージ・ライブラリ

### bcmath
固定小数点数の計算にbcmathを利用しています。

bcmathを有効にしてください。

- 参考: [BCMath](https://www.php.net/manual/ja/book.bc.php)

## 資料
- [課金・通貨基盤](https://wonderplanet.atlassian.net/wiki/spaces/wonderplanet/pages/106102785)(Confluence)
