<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Constants;

/**
 * マスターデータjsonファイル、管理ツールのマスターデータインポートなどで使用する値を定義している
 */
class MasterData
{
    public const MASTERDATA = 'masterdata';
    public const OPERATIONDATA = 'operationdata';
    public const MASTERDATA_I18N = 'mst_I18n';
    public const OPERATIONDATA_I18N = 'opr_I18n';
    public const MASTERDATA_I18N_PATH = 'masteri18ndata';
    public const OPERATIONDATA_I18N_PATH = 'operationi18ndata';

    /**
     * 配信として扱うリリース情報の件数
     * 下記条件に当てはまるデータを配信中ステータスとみなしており、最下部の条件に使用します
     *  ・enabled=1である
     *  ・target_release_version_idにnull以外が設定されている
     *  ・最新のrelease_key2つまで
     */
    public const MASTER_RELEASE_APPLY_LIMIT = 2;
}
