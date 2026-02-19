<?php

namespace App\Constants;

enum MasterDataImportStatus: string
{
    case READY = 'ready'; // 開始前
    case CSV_CREATED = 'csv_created'; // csv生成済み
    case COMMITED = 'commited'; // git差分のコミット済み
    case DB_CREATED = 'db_created'; // DB作成済み
    case SERIALIZED_FILE_UPLOADED = 'serialized_file_uploaded'; // S3へのアップロード済み
    case DB_IMPORTED = 'db_imported'; // DBへのデータ投入済み
    case FINISHED = 'finished'; // ReleaseControlへ登録し、処理完了
    case ROLLBACKED = 'rollbacked'; // ロールバック済み
}
