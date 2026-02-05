<?php

declare(strict_types=1);

/**
 * マスターアセット管理基盤で参照する設定
 */
return [
    /**
     * テーブル名の設定
     *
     * テーブル名は各プロダクトごとに変更される可能性があるため、ここで定義する
     *
     * キー値には、マスターアセット管理基盤で使用するテーブル名を指定する。
     * このテーブル名をマスターアセット管理基盤基盤ではデフォルト値とする。(ドキュメントなどでは、このデフォルトテーブル名を使用する)
     * 対応する値には、読み替える先のプロダクトごとのテーブル名を指定する。
     */
    'tablenames' => [
        'mng_master_releases' => 'mng_master_releases',
        'mng_master_release_versions' => 'mng_master_release_versions',
        'mng_asset_releases' => 'mng_asset_releases',
        'mng_asset_release_versions' => 'mng_asset_release_versions',
    ],

    // apiヘッダーのクライアントバージョンパラメータ名
    'header_client_version' => 'Client-Version',
];
