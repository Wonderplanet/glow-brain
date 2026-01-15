# Laravel Wonderplanet Framework 課金基盤ライブラリ

課金・通貨基盤において課金処理をまとめたライブラリです。

## 概要

通貨に絡まない課金処理を行います。

商品購入やストアのレシート検証など。

## 使用方法など
※使用方法や準備内容、データベース定義などはlaravel-wp-currencyと共通しています。
詳細はそちらのREADMEなどを参照してください

※本ライブラリはlaravel-wp-commonライブラリを使用しています。

更新を行う場合、laravel-wp-commonライブラリもバージョンを合わせて更新してください。

## 各ストアの設定について

レシートの検証などに使用するための、各ストア向けの設定は.envとwp_currency.phpで行います。

### AppStore

.envの次の設定を変更してください。
```
# バンドルID(Production)
APPSTORE_BUNDLE_ID_PRODUCTION=(bundle_idを記載)
# バンドルID(Sandbox)
APPSTORE_BUNDLE_ID_SANDBOX=(sandboxのbundle_idを記載)
```

#### 環境変数に設定する場合について

ECSなどでSecret Managerから注入するなど、環境変数に値を格納する場合があります。

その際は.envのキーをそのまま環境変数で使用してください。

### GooglePlayStore

.envの次の設定を変更してください。
```
# パッケージ名(production/sandbox)
GOOGLEPLAY_PACKAGE_NAME=(package_nameを記載)
# 購入レシート検証用の秘密鍵情報
GOOGLEPLAY_PURCHASE_CREDENTIAL=(購入レシート検証用の秘密鍵JSONファイルを記載)
# レシート検証用の公開鍵ファイル
GOOGLEPLAY_PUBKEY =(レシート検証用の公開鍵ファイルを記載)
```

#### GOOGLEPLAY_PURCHASE_CREDENTIALについて
[APIキーの取得](https://wonderplanet.atlassian.net/wiki/spaces/wonderplanet/pages/130056204/GCP+Play+Console+API#API%E3%82%AD%E3%83%BC%E3%81%AE%E5%8F%96%E5%BE%97) で取得した鍵ファイルを設置したパスです。

プロダクトの構成によってファイルの配置が違いますので、設置したパスをここに記載してください。

`※鍵ファイルをリポジトリにコミットしないよう、注意をしてください`

#### GOOGLEPLAY_PUBKEYについて

クライアントから送信されてきたレシートの検証に使用する公開鍵になります。
Play Consoleの「収益化のセットアップ > Google Play 請求サービス」にあるRSA 公開鍵をファイルに保存し、そのファイルパスを指定します。

`※鍵ファイルおよび文字列をリポジトリにコミットしないよう、注意をしてください`

公開鍵の文字列をファイルに保存する際には、次のように「-----BEGIN PUBLIC KEY-----」と「-----END PUBLIC KEY-----」で囲んでください。
```
-----BEGIN PUBLIC KEY-----
MII***〜
-----END PUBLIC KEY-----
```

#### 環境変数で設定する場合について
ECSなどでSecret Managerから注入するなど、環境変数に値を格納する場合があります。

GOOGLEPLAY_PACKAGE_NAMEについては、キーをそのまま使用してください。

GOOGLEPLAY_PURCHASE_CREDENTIALについては、`GOOGLEPLAY_PURCHASE_CREDENTIAL_ENV`に設定してください。

GOOGLEPLAY_PUBKEYについては、 `GOOGLEPLAY_PUBKEY_ENV` に設定してください。

またSecret Managerでは文字列に改行を含めることができないため、GOOGLEPLAY_PUBKEY_ENVには「\n」で改行を示すようにしてください。

課金ライブラリでは次の形式に対応しています。
```
-----BEGIN PUBLIC KEY-----\nMII***〜\n-----END PUBLIC KEY-----
```
「\n」の記載がない場合も、改行を付与します。
```
-----BEGIN PUBLIC KEY-----MII***〜-----END PUBLIC KEY-----
```
BEGINとENDがない場合はライブラリ側で補完します。
```
MII***〜
```
その他のパターンはGooglePlayPlatformServiceTestのテストケースを参照ください。

### ストアのユニットテスト

#### ユニットテスト用環境変数の入力について
ユニットテストでは.envを読み込まないため、各環境変数を設定しexportしてください。

#### 課金テストのスキップについて
レシート検証などストアに関わる処理は外部にアクセスするため、ユニットテストではスキップするようにしています。

ストア接続が必要となるテストケースには次のグループをつけていますので、phpunit.xmlでexcludeしてください。
- wp_billing_appstore
- wp_billing_googleplay

また、.envに設定している内容も環境変数に設定する必要があります。
.env.local_testファイルがリポジトリにコミットされているため、そこに記載するとリポジトリに登録されてしまうので、それを避けるために環境変数を使用しています。

これらのテストを確認する場合、必要な環境変数の設定などを行った後、引数--groupで指定してください。

#### Androidテスト用レシートの採取
Androidでユニットテストを行う場合、レシートの採取方法が少し特殊になります。

1. POSTリクエストをファイルに保存します
2. ファイルからJSON文字列を読み込み、receiptの中身を抜き出します

このときエスケープがうまく整合性取れないようで、`\\"`を`\\\"`に置き換える必要があります。
```php
<?php

$params = file_get_contents('sample_receipt.json');

$data = json_decode($params, true);
$receipt = $data['receipt'];

// $receiptの\\"を\\\"に置き換える
// エスケープが動作してしまうため文字コードで記述する
$receipt = str_replace(hex2bin("5c5c22"), hex2bin("5c5c5c22"), $receipt);

echo $receipt;
```

3. 表示されたJSONをwp_currency.store_test.googleplay_store.googleplay_sandbox_receipt のファイルパスに保存します
4. ユニットテストを --group wp_billing_googleplay で実行します



## 資料
- [課金・通貨基盤](https://wonderplanet.atlassian.net/wiki/spaces/wonderplanet/pages/106102785)(Confluence)
