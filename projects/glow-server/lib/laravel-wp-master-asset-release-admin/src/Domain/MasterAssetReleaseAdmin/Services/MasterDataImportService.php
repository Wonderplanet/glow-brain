<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Services;

use App\Traits\MngCacheDeleteTrait;
use WonderPlanet\Domain\Admin\Operators\S3Operator;
use WonderPlanet\Domain\Admin\Trait\DatabaseTransactionTrait;
use WonderPlanet\Domain\Common\Enums\Language;
use WonderPlanet\Domain\Common\Utils\DBUtility;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\MngMasterReleaseKeyEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterImportHistory;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\MasterDataDBOperator;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Utils\MasterDataImportUtility;

/**
 * マスターデータインポートv2管理ツール用
 */
class MasterDataImportService
{
    use DatabaseTransactionTrait;
    use MngCacheDeleteTrait;

    public function __construct(
        private readonly GitCommitService $gitCommitService,
        private readonly DatabaseImportService $databaseImportService,
        private readonly MngMasterReleaseService $mngMasterReleaseService,
        private readonly S3Operator $s3Operator,
        private readonly MasterDataDBOperator $masterDataDBOperator,
    ) {
    }

    /**
     * スプレッドシートからのインポート実行
     *  1. masterdata_csvディレクトリの変更点をコミット
     *  2. リリースバージョンDBを生成してインポートを実行
     *  3. s3に生成済みのクライアントデータファイルとmysqldumpファイルをアップロード
     *  4. masterdata_csvのgit push
     *  5. マスターデータ管理テーブルを更新する
     *
     * @param string $importId
     * @param MngMasterReleaseKeyEntity $mngMasterReleaseKeyEntity
     * @param array $masterDataHashMap
     * @param array $masterDataI18nHashMap
     * @param array $operationDataHashMap
     * @param array $operationDataI18nHashMap
     * @param array $serverDbHashMap
     * @param array $dataHashMap
     * @param string $importAdmUserId
     * @return void
     * @throws \Throwable
     */
    public function executeImport(
        string $importId,
        MngMasterReleaseKeyEntity $mngMasterReleaseKeyEntity,
        array $masterDataHashMap,
        array $masterDataI18nHashMap,
        array $operationDataHashMap,
        array $operationDataI18nHashMap,
        array $serverDbHashMap,
        array $dataHashMap,
        string $importAdmUserId
    ): void {
        try {
            // masterdata git の差分をコミットする(差分がない場合はコミットされない)
            // 差分比較画面に遷移した際に差分があればmasterdata git 上でも差分がある想定
            $changed = $this->gitCommitService->commitSpreadSheetCsv('commit by admin');
            $gitRevision = $this->gitCommitService->getCurrentHash();

            // データベース用CSVをDBに投入する(環境間インポート用のdumpファイル生成もここでやる)
            $masterSchemaVersions = $this->databaseImportService->import($importId, $mngMasterReleaseKeyEntity, $serverDbHashMap);

            // 生成済みのクライアントjsonファイルとmasterdataのdumpファイルをs3にアップロード
            foreach ($mngMasterReleaseKeyEntity->getReleaseKeys() as $releaseKey) {
                // クライアントjsonファイル配置
                $serializedFileDirPath = config('wp_master_asset_release_admin.serializedFileDir') . "/{$importId}/{$releaseKey}/";

                // serializedFileDir以下を丸々S3へアップロード
                // 各マスターのjsonはこちらが参照される
                // release_keyごとのディレクトリで保存する
                // 例：<s3アセットパス>/{release_key}/masterdata/masterdata_{hash}.json
                $this->s3Operator->uploadDirectory($serializedFileDirPath, S3Operator::CONFIG_NAME_S3, "{$releaseKey}/");

                // masterdataのdumpファイル配置
                $masterDataMysqlDump = config('wp_master_asset_release_admin.masterDataMysqlDump') . "/{$importId}/{$releaseKey}/";

                // masterDataMysqlDump以下を丸々S3へアップロード
                // release_keyごとのディレクトリで保存する
                $this->s3Operator->uploadDirectory($masterDataMysqlDump, config('wp_master_asset_release_admin.master_data_mysqldump_bucket'), "{$releaseKey}/");
            }

            // マスターデータ管理テーブルの更新
            $this->transaction(function () use (
                $mngMasterReleaseKeyEntity,
                $gitRevision,
                $masterDataHashMap,
                $masterDataI18nHashMap,
                $operationDataHashMap,
                $operationDataI18nHashMap,
                $masterSchemaVersions,
                $serverDbHashMap,
                $dataHashMap,
                $importAdmUserId,
            ) {
                $this->mngMasterReleaseService->updateMasterRelease(
                    $mngMasterReleaseKeyEntity->getReleaseKeys(),
                    $gitRevision,
                    $masterDataHashMap,
                    $masterDataI18nHashMap,
                    $operationDataHashMap,
                    $operationDataI18nHashMap,
                    $masterSchemaVersions->toArray(),
                    $serverDbHashMap,
                    $dataHashMap,
                    $importAdmUserId,
                    AdmMasterImportHistory::IMPORT_SOURCE_SPREAD_SHEET
                );
            }, [DBUtility::getMngConnName(), DBUtility::getAdminConnName()]);

            // キャッシュを削除
            $this->deleteMngMasterReleaseVersionCache();

            // 更新処理が完了したので、取り込んだ差分をmasterdataのgitにプッシュする
            if ($changed) $this->gitCommitService->pushSpreadSheetCsv();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * 環境間インポート実行
     *  1. s3に配置されているインポート元環境のmysqldumpを取得
     *  2. mysqldumpを元にリリースバージョンDBを作成する
     *  3. s3に配置しているインポート元環境のリリース対象のjsonファイルを自環境のバケットにコピーする
     *  4. マスターデータ管理テーブルをインポート元環境の情報を元に更新する
     *
     * @param string $importId
     * @param string $fromEnvironment
     * @param array $fromEnvironmentMasterReleaseVersionMap
     * @param string $importAdmUserId
     * @return void
     * @throws \Throwable
     */
    public function executeImportFromEnvironment(
        string $importId,
        string $fromEnvironment,
        array $fromEnvironmentMasterReleaseVersionMap,
        string $importAdmUserId
    ): void {
        try {
            $releaseKeys = [];
            $gitRevision = '';
            $masterDataHashMap = [];
            $masterDataI18nHashMap = [];
            $operationDataHashMap = [];
            $operationDataI18nHashMap = [];
            $masterSchemaVersions = [];
            $serverDbHashMap = [];
            $dataHashMap = [];

            $mysqldumpConfigName = MasterDataImportUtility::getFromEnvironmentMySqlDumpConfigName($fromEnvironment);
            $clientMasterDataConfigName = MasterDataImportUtility::getFromEnvironmentClientMasterDataConfigName($fromEnvironment);

            foreach ($fromEnvironmentMasterReleaseVersionMap as $releaseKey => $fromEnvironmentMasterReleaseVersion) {
                // s3に配置されているインポート元環境のmysqldumpを取得
                \Log::info('s3 mysqldump dwonload start releaseKey: ' . $releaseKey);

                $prefix = MasterDataImportUtility::getS3MysqlDumpFilePrefix($fromEnvironment);
                $serverDbHashMap[$releaseKey] = $fromEnvironmentMasterReleaseVersion['server_db_hash'];
                $s3FilePath = "{$releaseKey}/" . $prefix . '_' . $this->masterDataDBOperator->getMasterDbNameNoPrefix($releaseKey, $serverDbHashMap) . '.sql';
                $downloadFilePath = config('wp_master_asset_release_admin.downloadMasterDataMysqlDump') . "/{$importId}/{$s3FilePath}";
                $this->s3Operator->downloadMasterMySqlDump($mysqldumpConfigName, $s3FilePath, $downloadFilePath);

                \Log::info('s3 mysqldump download end file: ' . $downloadFilePath);

                // mysqldumpを元にリリースバージョンDB生成を実行(同名のDBが存在していた場合は削除して作り直している)
                \Log::info('generate db start releaseKey: ' . $releaseKey);

                $serverDbHashMap[$releaseKey] = $fromEnvironmentMasterReleaseVersion['server_db_hash'];
                $this->databaseImportService->importFromEnvironment($releaseKey, $serverDbHashMap, $downloadFilePath);

                \Log::info('generate db end releaseKey: ' . $releaseKey);

                // s3に配置しているインポート元環境のマスターデータファイルをコピー
                \Log::info('copy masterdata file start releaseKey: ' . $releaseKey);

                // コピー対象のファイルパスリストを取得(jsonファイル含む)
                $hashPathList = MasterDataImportUtility::getMasterDataHashPathList($fromEnvironmentMasterReleaseVersion);
                foreach ($hashPathList as $path) {
                    $filePath = "{$releaseKey}/{$path}";
                    $this->s3Operator->copyMasterDataFile($clientMasterDataConfigName, S3Operator::CONFIG_NAME_S3, $filePath);
                    \Log::info('copy masterdata file copied: ' . $filePath);
                }

                \Log::info('copy masterdata file end releaseKey: ' . $releaseKey);

                // 登録データ生成
                if ($gitRevision === '') {
                    // git_revisionは1回のインポートで同一なので、一度だけ設定する
                    $gitRevision = $fromEnvironmentMasterReleaseVersion['git_revision'];
                }
                $releaseKeys[] = $releaseKey;
                $serverDbHash = $fromEnvironmentMasterReleaseVersion['server_db_hash'];
                $serverDbHashMap[$releaseKey] = $serverDbHash;

                $dataHashMap[$releaseKey] = $fromEnvironmentMasterReleaseVersion['data_hash'];

                $masterDataHashMap[$releaseKey] = $fromEnvironmentMasterReleaseVersion['client_mst_data_hash'];
                $masterDataI18nHashMap[$releaseKey] = [
                    Language::Ja->value => $fromEnvironmentMasterReleaseVersion['client_mst_data_i18n_ja_hash'],
                    Language::En->value => $fromEnvironmentMasterReleaseVersion['client_mst_data_i18n_en_hash'],
                    Language::Zh_Hant->value => $fromEnvironmentMasterReleaseVersion['client_mst_data_i18n_zh_hash'],
                ];

                $operationDataHashMap[$releaseKey] = $fromEnvironmentMasterReleaseVersion['client_opr_data_hash'];
                $operationDataI18nHashMap[$releaseKey] = [
                    Language::Ja->value => $fromEnvironmentMasterReleaseVersion['client_opr_data_i18n_ja_hash'],
                    Language::En->value => $fromEnvironmentMasterReleaseVersion['client_opr_data_i18n_en_hash'],
                    Language::Zh_Hant->value => $fromEnvironmentMasterReleaseVersion['client_opr_data_i18n_zh_hash'],
                ];

                $dbName = config('app.env') . '_' . MasterDataDBOperator::MASTER_DATA_DB_PREFIX . "{$releaseKey}_{$serverDbHash}";
                $masterSchemaVersions[$dbName] = $fromEnvironmentMasterReleaseVersion['master_schema_version'];

                // 環境間コピーした環境からさらに環境間コピーできるようにdumpファイルをs3にアップロード
                $configName = config('wp_master_asset_release_admin.master_data_mysqldump_bucket');
                $s3FilePath = "{$releaseKey}/{$dbName}.sql";
                $this->s3Operator->uploadDownloadMasterMySqlDump($configName, $s3FilePath, $downloadFilePath);
            }

            // マスターデータ管理テーブルの更新
            $this->transaction(function () use (
                $releaseKeys,
                $gitRevision,
                $masterDataHashMap,
                $masterDataI18nHashMap,
                $operationDataHashMap,
                $operationDataI18nHashMap,
                $masterSchemaVersions,
                $serverDbHashMap,
                $dataHashMap,
                $importAdmUserId,
            ) {
                $this->mngMasterReleaseService->updateMasterRelease(
                    $releaseKeys,
                    $gitRevision,
                    $masterDataHashMap,
                    $masterDataI18nHashMap,
                    $operationDataHashMap,
                    $operationDataI18nHashMap,
                    $masterSchemaVersions,
                    $serverDbHashMap,
                    $dataHashMap,
                    $importAdmUserId,
                    AdmMasterImportHistory::IMPORT_SOURCE_FROM_ENVIRONMENT
                );
            }, [DBUtility::getMngConnName(), DBUtility::getAdminConnName()]);

            // キャッシュを削除
            $this->deleteMngMasterReleaseVersionCache();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
