## データベースのマイグレーションについて

このマイグレーションは管理ツールで使用するマイグレーションを格納します。

本ライブラリで必要なマイグレーションはmigrationsに格納します。

## パッケージのマイグレーションファイルをそのまま使用する場合
マイグレーションファイルは `artisan vendor:publish --tag wp-admin` でadmin側のmigrationsにコピーされます

## マイグレーションファイル記述の注意点

マイグレーションファイルを作成するにあたって、いくつかの注意点があります。

詳細は[GitHub Wikiのガイドライン](https://github.com/Wonderplanet/laravel-wp-framework/wiki/%E9%96%8B%E7%99%BA%E3%82%AC%E3%82%A4%E3%83%89%E3%83%A9%E3%82%A4%E3%83%B3#db%E3%83%9E%E3%82%A4%E3%82%B0%E3%83%AC%E3%83%BC%E3%82%B7%E3%83%A7%E3%83%B3%E3%83%95%E3%82%A1%E3%82%A4%E3%83%AB%E3%81%AB%E3%81%A4%E3%81%84%E3%81%A6)を参照してください。

## migrationsテーブルの更新について

このマイグレーションについては、元々実行済みマイグレーションファイルの名称を変更している為、プロダクトによってはすでに実行済みの可能性があります。  
実行済みの可能性がある場合、migrationsテーブルに対してUPDATEクエリを実行してください。

```
UPDATE  migrations SET migration = '2024_09_03_052407_wp_master_asset_release_admin_create_adm_master_import_tables' WHERE migration = '2024_09_03_052407_create_adm_master_import_tables';
UPDATE  migrations SET migration = '2024_09_25_070209_wp_master_asset_release_admin_create_adm_asset_import_tables' WHERE migration = '2024_09_25_070209_create_adm_asset_import_tables';
UPDATE  migrations SET migration = '2024_11_07_114332_wp_master_asset_release_admin_rename_opr_to_mng_for_release_controls' WHERE migration = '2024_11_07_114332_rename_opr_to_mng_for_release_controls';
```
