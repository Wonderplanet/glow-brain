<?php

namespace App\Services\MasterData;

use App\Constants\Database;
use App\Constants\MasterDataImportStatus;
use App\Models\Adm\AdmDataControl;
use App\Services\MasterData\DatabaseCsvGenerateService;
use App\Services\MasterData\DatabaseImportService;
use App\Services\MasterData\GitCommitService;
use App\Services\MasterData\MasterDataImportStatusService;
use App\Services\MasterData\OprMasterReleaseControlAccessService;
use App\Services\MasterData\SerializeDataUploadService;
use App\Services\MasterData\SpreadSheetFetchService;
use Illuminate\Support\Facades\DB;

class MasterDataImportService
{

    private SpreadSheetFetchService $spreadSheetFetchService;
    private GitCommitService $gitCommitService;
    private DatabaseCsvGenerateService $databaseCsvGenerateService;
    private SerializeDataUploadService $serializeDataUploadService;
    private DatabaseImportService $databaseImportService;
    private MasterDataImportStatusService $masterDataImportStatusService;
    private OprMasterReleaseControlAccessService $oprMasterReleaseControlAccessService;
    public function __construct()
    {
        $this->spreadSheetFetchService = new SpreadSheetFetchService();
        $this->gitCommitService = new GitCommitService();
        $this->databaseCsvGenerateService = new DatabaseCsvGenerateService();
        $this->serializeDataUploadService = new SerializeDataUploadService();
        $this->databaseImportService = new DatabaseImportService();
        $this->masterDataImportStatusService = new MasterDataImportStatusService();
        $this->oprMasterReleaseControlAccessService = new OprMasterReleaseControlAccessService();
    }

    public function executeImport()
    {
        // 投入対象テーブルが増えて処理時間が長くなり、502エラーが多発しているので、時間制限を延長する
        ini_set('max_execution_time', 300); // 5分

        $control = $this->masterDataImportStatusService->ready();

        try {
            // スプレッドシートCSVの差分をコミットしてハッシュを取得できるようにしておく
            DB::connection(Database::TIDB_CONNECTION)->beginTransaction();

            // バリデーション用のCSVを生成して書き出し
            $this->databaseCsvGenerateService->generateRawDatabaseCsv();
            $control = $this->masterDataImportStatusService->csvCreated();

            $changed = $this->gitCommitService->commitSpreadSheetCsv('commit by admin');
            $control = $this->masterDataImportStatusService->commited();

            // データベース用のCSVを生成して書き出し＋DBを作成しておく
            $this->databaseCsvGenerateService->generateDatabaseCsv();
            $control = $this->masterDataImportStatusService->dbCreated();

            // クライアント用のJsonをデータベース用CSVから生成して書き出し
            $this->serializeDataUploadService->upload();
            $control = $this->masterDataImportStatusService->uploaded();

            // データベース用CSVをDBに投入して、リリースコントロールを更新する
            $this->databaseImportService->import();
            $control = $this->masterDataImportStatusService->dbImported();

            // 更新処理が完了したので、取り込んだ差分をGitにプッシュする
            if ($changed) $this->gitCommitService->pushSpreadSheetCsv();
            $control = $this->masterDataImportStatusService->finished();
            DB::connection(Database::TIDB_CONNECTION)->commit();
        } catch (\Throwable $e) {
            DB::connection(Database::TIDB_CONNECTION)->rollBack();
            $this->rollback($control);
            $this->masterDataImportStatusService->rollbacked();
            throw $e;
        }
    }

    /**
     * GitのコミットからデータをDBに投入する
     * @param string $checkoutTarget
     * @param bool $isBranchName
     * @return void
     * @throws \Throwable
     */
    public function executeApply(string $checkoutTarget, bool $isBranchName = true)
    {
        $control = $this->masterDataImportStatusService->ready();

        try {
            DB::connection(Database::TIDB_CONNECTION)->beginTransaction();

            // ブランチorハッシュをチェックアウト
            if ($isBranchName) {
                $this->gitCommitService->checkoutBranch($checkoutTarget);
            } else {
                $this->gitCommitService->checkoutHash($checkoutTarget);
            }
            $control = $this->masterDataImportStatusService->commited();

            // データベース用のCSVを生成して書き出し＋DBを作成しておく
            $this->databaseCsvGenerateService->generateDatabaseCsv();
            $control = $this->masterDataImportStatusService->dbCreated();

            // クライアント用のJsonをデータベース用CSVから生成して書き出し
            $this->serializeDataUploadService->upload();
            $control = $this->masterDataImportStatusService->uploaded();

            // データベース用CSVをDBに投入して、リリースコントロールを更新する
            $this->databaseImportService->import();
            $control = $this->masterDataImportStatusService->finished();
            DB::connection(Database::TIDB_CONNECTION)->commit();
        } catch (\Throwable $e) {
            DB::connection(Database::TIDB_CONNECTION)->rollBack();
            $this->rollback($control);
            $this->masterDataImportStatusService->rollbacked();
            throw $e;
        }
    }


    public function rollback(AdmDataControl $control)
    {
        // ステータスの更新逆順にロールバック処理を実行していく
        switch ($control->getStatus()) {
            case MasterDataImportStatus::DB_IMPORTED:
                // DBへのデータ投入までしていたとしても後段の処理でDB削除する
                // リリースコントロールはトランザクションのロールバックで元に戻す
                // fall through
            case MasterDataImportStatus::SERIALIZED_FILE_UPLOADED:
                // アップロードしたファイルはそのままにしておく
                // fall through
            case MasterDataImportStatus::DB_CREATED:
                // DBを作成してしまっていたら、該当のDBを削除する
                $this->databaseCsvGenerateService->rollback();
                // fall through
            case MasterDataImportStatus::COMMITED:
                // gitのコミットを現在適用しているブランチ追従する
                $this->gitCommitService->resetSpreadSheetCsv();
                // fall through
        }
    }
}
