<?php

namespace App\Models\Adm;

use App\Constants\Database;
use App\Constants\MasterDataImportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ロック用のテーブル
 */
class AdmDataControl extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = Database::ADMIN_CONNECTION;

    protected $fillable = ['id', 'control_type', 'version', 'status', 'data', 'deleted_at'];

    protected $casts = [
        'status' => MasterDataImportStatus::class,
    ];

    public function getStatus(): ?MasterDataImportStatus
    {
        return $this->status;
    }
    public function getStatusName(): ?string
    {
        switch ($this->status)
        {
            case MasterDataImportStatus::READY:
                return "開始前 (1/6)";
            case MasterDataImportStatus::COMMITED:
                return "Git差分コミット済み (2/6)";
            case MasterDataImportStatus::DB_CREATED:
                return "DB作成済み (3/6)";
            case MasterDataImportStatus::SERIALIZED_FILE_UPLOADED:
                return "S3へのアップロード済み (4/6)";
            case MasterDataImportStatus::DB_IMPORTED:
                return "DBへのデータ投入済み (5/6)";
            case MasterDataImportStatus::FINISHED:
                return "データ投入完了";
            case MasterDataImportStatus::ROLLBACKED:
                return "エラー終了";
        }
        // 未定義ステータス
        return $this->status;
    }


    public function isImporting(): bool
    {
        return $this->getStatus() !== MasterDataImportStatus::FINISHED && $this->getStatus() !== MasterDataImportStatus::ROLLBACKED;
    }
    public function isFinished(): bool
    {
        return is_null($this->getStatus()) || $this->getStatus() === MasterDataImportStatus::FINISHED || $this->getStatus() === MasterDataImportStatus::ROLLBACKED;
    }
}
