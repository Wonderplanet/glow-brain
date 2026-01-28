<?php

namespace App\Constants;

class BankKPIConstant
{
    public const VERSION = '1.0';

    // 一回のログ処理件数
    public const LOG_FETCH_LIMIT = 5000;
    // 一回の一括挿入件数
    public const INSERT_CHUNK_SIZE = 1000;

    // 検証環境Fluentdタグ
    public const FLUENTD_TAG_STG = 'bng.kpi.gs.stg.%s.%s';
    // 本番環境Fluentdタグ
    public const FLUENTD_TAG_PRD = 'bng.kpi.gs.prd.%s.%s';

    // 一時保存用ファイル置き場
    public const DISK_TEMP = 'bank_kpi_temp';
    // f001用ファイル置き場
    public const DISK_F001 = 'bank_kpi_f001';
    // f001用ファイル置き場
    public const DISK_F002 = 'bank_kpi_f002';
    // s3バケット
    public const DISK_S3 = 'bank_kpi_s3';
    // f001用ファイル置き場
    public const DISK_F003_DAILY = 'bank_kpi_f003_daily';
    // f001用ファイル置き場
    public const DISK_F003_MONTHLY = 'bank_kpi_f003_monthly';
    // 一時ファイル名フォーマット(フォーマット_環境.YYYYMMDDHHIISS.log)
    public const TEMP_FILE_NAME_FORMAT = '%s_%s.%s.log';
    // 圧縮ファイル名フォーマット(環境.YYYYMMDDHHIISS.gz)
    public const COMPRESS_FILE_NAME_FORMAT = '%s.%s.gz';
    // s3アップロードパス
    // data/{app_id}/{YYYY}/{MM}/{DD}/{HH}/{format_id}/ファイル名
    public const S3_UPLOAD_PATH = 'data/%s/%s/%s/%s/%s/%s/%s';

    public const PATTERN_AD_ID = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/';

    // 広告IDが取得できなかった場合のデフォルト値
    public const DEFAULT_AD_ID = '00000000-0000-0000-0000-000000000000';

    public const PLATFORM_ASB = 'asb';
}
