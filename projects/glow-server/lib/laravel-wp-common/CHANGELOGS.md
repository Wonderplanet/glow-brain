# CHANGELOGS

本ディレクトリ以下の修正について記載します。
マージ後に、関連するPRを記載してきます。

## 2025/06/12
- [改善] ReflectionTraitをcommonライブラリに作る #1318
- [メッセージ配布][ロギング基盤]log_messagesにログ共通カラムを追加 #1316

## 2025/06/05
- [task] OprProductの統合 #1301

## 2025/05/28
- [管理ツール][全体メンテナンス]全体メンテナンスライブラリと画面UIの実装 #1285

## 2025/05/20
- [task] mst, oprテーブルにAPCuキャッシュ機構を適用する #1277
- MasterRepositoryを移動 #1275

## 2025/05/08
- [bugfix][課金基盤] 課金基盤ライブラリのRequestIdをoctane向けに修正する  #1259

## 2025/05/01
- Eloquent/Services内にあるRepository的なロジックをRepositoryに移動 #1239

## 2025/04/24
- [課金基盤] 課金基盤ライブラリのgetNginxRequestIdがoctaneで取得できない #1252

## 2025/04/22
- [task] phpunitの対応 #1248

## 2025/04/17
- [feature]Laravel12へのアップデート #1247

## 2025/04/15
- [課金基盤] 接続先DBの設定を環境変数から取る(wp_commonに合わせる)  #1246

## 2025/03/24
- BaseImportCsvで時間系のcasts対応 #1227

## 2025/03/06
- [改善] APCuがcliでONのときにユニットテストが失敗しないようにする #1213

## 2025/02/27
- マスターアセットクライアントバージョン後方互換対応のfeatureをdevelopにマージ #1204

## 2025/02/25
- [api]マスターリリース後方互換バージョン対応の修正 ミドルウェアで例外が発生した際にエラーコードを返すようにした #1200
- [改善] deptracのyamlにUtilなどを追加 #1202

## 2025/02/12
- [管理ツール]マスターデータ管理後方互換対応 api関連の修正 #1157

## 2025/02/04
- [task] lib以下にDatadogの計装用コードをまとめる #1139

## 2025/02/03
- マスターアセットライブラリ化したfeatureをdevelopにマージ #1127

## 2025/01/29
- マスターアセットリリース管理ツールv2機能をライブラリ化 #1116

## 2025/01/23
- マスターリリース関連のコードを管理するをライブラリを作成してコードを移動 #1105

## 2025/01/09
- [feature] mng_master_relesesとmng_master_release_versionsにcreatd_atとupdated_atを追加する #1074

## 2024/12/19
- [messagePack] jsonからの移行対応 #1021

## 2024/12/16
- Taguchi/feature/940 message pack #1014

## 2024/12/04
-  [DIの改善] 課金基盤のDelegatorをscopedに変更、Facade追加 #988

## 2024/11/29
- [DIの改善] IAA基盤のFacadeをscopedに変更、Facadeのキーをconst化 #974

## 2024/11/28
- mng_master_release_versions.master_scheme_versionをリネーム #971
- DIの改善  #968

## 2024/11/19
- [MessagePack対応] game/version APIでmessagePackのファイルパスを返す #949

## 2024/11/18
- merge Feature/sprint37_api_master_import_v2 to develop #944

## 2024/11/15
- wp-commonのBaseModelをcommonディレクトリに統合するように修正 #943
- 最新のdevelopをfeatureに取り込む対応 #942

## 2024/11/14
- wp-common/MasterRelease作成対応 #935

## 2024/11/13
- MngMasterReleaseとMngMasterReleaseVersionのデータ取得周りのリファクタリング #933

## 2024/11/12
- [bug] ギフト配布でツールから追加後にキャッシュが更新されない #931

## 2024/11/08
- MngMaster/MngAsset系のDB接続をapiと同じに修正 #927

## 2024/11/07
- developの取り込みとrelease control系テーブルをmngにリネームするの追加対応 #924
- リリースバージョンごとにDBを切り替える仕組みを実装 #921

## 2024/10/31
- [キャッシング機構] ギフト/メッセージ配布でキャッシュが更新されるように対応する #910
- [キャッシング機構] MasterRepository内のキャッシュ機構をcommon libraryに移動 #909

## 2024/10/22
- telescopeにBasic認証を設定 #901

## 2024/09/27
- APIおよびテストの実行時間を固定する  #855

## 2024/09/12
- [ユニットテスト]fixtureデータ読み込みをfactory経由に対応した #808

## 2024/07/01
- [IAA基盤] サーバーライブラリの雛形作成 #612

## 2024/06/19
- 共通ライブラリのcomposer化 #559
