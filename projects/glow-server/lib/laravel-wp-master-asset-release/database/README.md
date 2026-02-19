## データベースのマイグレーションについて

このマイグレーションはAPI/管理ツールで使用するマイグレーションを格納します。

本ライブラリで必要なマイグレーションはmigrationsに格納します。

## パッケージのマイグレーションファイルをそのまま使用する場合
マイグレーションファイルは `artisan vendor:publish --tag wp` でプロダクト側のmigrationsにコピーされます

## マイグレーションファイル記述の注意点

マイグレーションファイルを作成するにあたって、いくつかの注意点があります。

詳細は[GitHub Wikiのガイドライン](https://github.com/Wonderplanet/laravel-wp-framework/wiki/%E9%96%8B%E7%99%BA%E3%82%AC%E3%82%A4%E3%83%89%E3%83%A9%E3%82%A4%E3%83%B3#db%E3%83%9E%E3%82%A4%E3%82%B0%E3%83%AC%E3%83%BC%E3%82%B7%E3%83%A7%E3%83%B3%E3%83%95%E3%82%A1%E3%82%A4%E3%83%AB%E3%81%AB%E3%81%A4%E3%81%84%E3%81%A6)を参照してください。

## migrationsテーブルの更新について

このマイグレーションについては、元々実行済みマイグレーションファイルの名称を変更している為、プロダクトによってはすでに実行済みの可能性があります。  
実行済みの可能性がある場合、migrationsテーブルに対してUPDATEクエリを実行してください。

```
UPDATE  migrations SET migration = '2024_09_02_070515_wp_master_asset_release_create_opr_master_release_tables' WHERE migration = '2024_09_02_070515_create_opr_master_release_tables';
UPDATE  migrations SET migration = '2024_09_18_063616_wp_master_asset_release_create_opr_asset_release_tables' WHERE migration = '2024_09_18_063616_create_opr_asset_release_tables';
UPDATE  migrations SET migration = '2024_09_20_054812_wp_master_asset_release_modify_opr_asset_releases_index' WHERE migration = '2024_09_20_054812_modify_opr_asset_releases_index';
UPDATE  migrations SET migration = '2024_09_25_113039_wp_master_asset_release_modify_opr_asset_releases_target_release_version_id' WHERE migration = '2024_09_25_113039_modify_opr_asset_releases_target_release_version_id';
UPDATE  migrations SET migration = '2024_10_15_053407_wp_master_asset_release_add_column_description_opr_mst_releases_and_opr_assert_releases' WHERE migration = '2024_10_15_053407_add_column_description_opr_mst_releases_and_opr_assert_releases';
UPDATE  migrations SET migration = '2024_11_07_114452_wp_master_asset_release_rename_opr_to_mng_for_release_controls' WHERE migration = '2024_11_07_114452_rename_opr_to_mng_for_release_controls';
UPDATE  migrations SET migration = '2024_11_28_034737_wp_master_asset_release_rename_column_master_scheme_version_to_master_schema_version_from_mng_master_release_versions' WHERE migration = '2024_11_28_034737_rename_column_master_scheme_version_to_master_schema_version_from_mng_master_release_versions';
UPDATE  migrations SET migration = '2025_01_09_103236_wp_master_asset_release_add_created_at_to_mng_release_tables' WHERE migration = '2025_01_09_103236_add_created_at_to_mng_release_tables';
```
