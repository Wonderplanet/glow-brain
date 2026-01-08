# CHANGELOGS

本ディレクトリ以下の修正について記載します。
マージ後に、関連するPRを記載してきます。

## 2025/06/12
- [改善] ReflectionTraitをcommonライブラリに作る #1318

## 2025/05/08
- [bugfix][課金基盤] 課金基盤ライブラリのRequestIdをoctane向けに修正する  #1259

## 2025/04/24
- [課金基盤] 課金基盤ライブラリのgetNginxRequestIdがoctaneで取得できない #1252

## 2025/04/23
- [task] deptracの警告とエラーの改善 #1250

## 2025/04/22
- [task] phpunitの対応 #1248

## 2025/04/17
- [feature]Laravel12へのアップデート #1247

## 2025/04/10
- [bugfix][課金基盤] UsrStoneInfoのpaid_priceをdecimalとして扱っているところをint(64)に変更 #1233

## 2025/03/20
- [feature][課金基盤] getStoreAllowance リファクタリング #1225

## 2025/03/19
- [feature][課金基盤] 購入日時の取得キーを変更する #1223

## 2025/03/14
- [task] Google PlayのAPIで購入失敗時にフラグを切り替えて通信する #1220

## 2025/03/07
- [task] buyで購入上限に引っかかった時、allowanceが作れなかった時にエラーとする #1216

## 2025/03/06
- [task] allowanceがnullのときに任意に作成するコールバックを組み込む #1197

## 2025/02/25
- [改善] deptracのyamlにUtilなどを追加 #1202

## 2025/01/09
- [feature]composer パッケージの更新 #1070

## 2024/12/04
-  [DIの改善] 課金基盤のDelegatorをscopedに変更、Facade追加 #988

## 2024/10/04
- Merge develop to feature/sprint34_86eqbxup4_debug_mode #870

※プログラムはbilling-wp-currencyに依存しています。
  そのためプログラムに影響する修正内容がbilling-wp-currencyに含まれている場合があります。
  billing-wp-currencyのCHANGELOGSも確認してください。
