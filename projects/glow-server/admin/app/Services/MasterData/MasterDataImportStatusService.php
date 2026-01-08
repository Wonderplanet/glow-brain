<?php

namespace App\Services\MasterData;

use App\Constants\DataControl as DataControlConst;
use App\Constants\MasterDataImportStatus;
use App\Models\Adm\AdmDataControl;

class MasterDataImportStatusService
{
    public function isImporting(): bool
    {
        $status = $this->findCurrentImportStatus();
        return !in_array($status->status, [MasterDataImportStatus::ROLLBACKED, MasterDataImportStatus::FINISHED]);
    }

    public function findCurrentImportStatus(): ?AdmDataControl
    {
        return AdmDataControl::where('control_type', DataControlConst::MASTER_DATA_IMPORT)->withTrashed()->first();
    }

    public function updateLockStatus(MasterDataImportStatus $status): AdmDataControl
    {
        $current = $this->findCurrentImportStatus();
        // インポート処理の開始タイミングで、既にインポートが実行中の場合はエラー
        if ($status === MasterDataImportStatus::READY) {
            if ($current != null && $current->isImporting()) {
                throw new \Exception('既にインポートが実行中です。');
            }
            $version = ($current->version ?? 0) + 1;
            AdmDataControl::upsert([
                'control_type' => DataControlConst::MASTER_DATA_IMPORT,
                'version' => $version,
                'status' => $status,
                'data' => null,
                'deleted_at' => null,
            ], ['control_type']);

            $current = $this->findCurrentImportStatus();
            if ($current->version != $version) {
                throw new \Exception('インポートが実行中です。');
            }
        } else {
            if ($current == null) {
                throw new \Exception('インポートが実行中ではありません。');
            }
            $current->status = $status;
            if ($current->isFinished()) {
                $current->deleted_at = now();
            }
            $current->save();
        }
        return $current;
    }

    public function ready(): AdmDataControl
    {
        return $this->updateLockStatus(MasterDataImportStatus::READY);
    }
    public function csvCreated(): AdmDataControl
    {
        return $this->updateLockStatus(MasterDataImportStatus::CSV_CREATED);
    }
    public function commited(): AdmDataControl
    {
        return $this->updateLockStatus(MasterDataImportStatus::COMMITED);
    }
    public function dbCreated(): AdmDataControl
    {
        return $this->updateLockStatus(MasterDataImportStatus::DB_CREATED);
    }
    public function uploaded(): AdmDataControl
    {
        return $this->updateLockStatus(MasterDataImportStatus::SERIALIZED_FILE_UPLOADED);
    }
    public function dbImported(): AdmDataControl
    {
        return $this->updateLockStatus(MasterDataImportStatus::DB_IMPORTED);
    }
    public function finished(): AdmDataControl
    {
        return $this->updateLockStatus(MasterDataImportStatus::FINISHED);
    }
    public function rollbacked(): AdmDataControl
    {
        return $this->updateLockStatus(MasterDataImportStatus::ROLLBACKED);
    }

}
