<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\Admin\Services\SendApiService;
use WonderPlanet\Domain\Common\Enums\Language;
use WonderPlanet\Domain\MasterAssetRelease\Constants\MasterData;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterImportHistory;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmMasterImportHistoryVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;

/**
 * マスターデータ管理ツールv2用のサービスクラス
 */
class MngMasterReleaseService
{
    public function __construct(
        readonly private SendApiService $sendApiService,
    ) {
    }

    /**
     * 最も最新の配信中ステータスのMngMasterReleaseを取得する
     *
     * @return MngMasterRelease|null
     */
    public function getLatestReleasedMngMasterRelease(): ?MngMasterRelease
    {
        return MngMasterRelease::query()
            ->where('enabled', 1)
            ->whereNotNull('target_release_version_id')
            ->orderBy('release_key', 'desc')
            ->limit(1)
            ->get()
            ->first();
    }

    /**
     * 配信中ステータスのMngMasterReleaseを取得する
     * 下記条件に当てはまるデータを配信中ステータスとみなします
     *  ・enabled=1である
     *  ・target_release_version_idにnull以外が設定されている
     *  ・最新のrelease_key2つまで
     *
     * @return Collection
     */
    public function getMngMasterReleasesByApply(): Collection
    {
        return MngMasterRelease::query()
            ->where('enabled', 1)
            ->whereNotNull('target_release_version_id')
            ->orderBy('release_key', 'desc')
            ->limit(MasterData::MASTER_RELEASE_APPLY_LIMIT)
            ->get();
    }

    /**
     * 配信中ステータスのうち一番古いMngMasterReleaseを取得する
     *
     * @return MngMasterRelease|null
     */
    public function getOldestApplyMngMasterRelease(): ?MngMasterRelease
    {
        return $this->getMngMasterReleasesByApply()
            ->sortBy('release_key')
            ->first();
    }

    /**
     * 配信中/配信準備中のMngMasterReleaseを取得する
     *
     * @return Collection
     */
    public function getMngMasterReleasesByApplyOrPending(): Collection
    {
        // 配信中ステータスのrelease_keyのうち、一番古いものを取得(なければ0)
        $oldestReleaseKey = $this->getOldestApplyMngMasterRelease()->release_key ?? 0;
        return MngMasterRelease::query()
            ->whereNot(function ($query) use ($oldestReleaseKey) {
                // 「配信中」または「配信準備中」データを取得
                // 「配信終了」以外のデータを取得する条件としている
                $query
                    ->where('enabled', 1)
                    ->whereNotNull('target_release_version_id')
                    ->where('release_key', '<', $oldestReleaseKey);
            })
            ->orderBy('release_key', 'desc')
            ->get();
    }

    /**
     * adm_master_import_historiesとadm_master_import_history_versionsから、mng_master_release_version_idをキーにした
     * last import at(created_at)のマップを取得する
     *
     * @return array
     */
    public function getLastImportAtMap(): array
    {
        $result = [];
        $admMasterImportHistories = AdmMasterImportHistory::query()
            ->select(
                'adm_master_import_history_versions.mng_master_release_version_id',
                'adm_master_import_histories.created_at'
            )
            ->join(
                'adm_master_import_history_versions',
                'adm_master_import_histories.id',
                '=',
                'adm_master_import_history_id'
            )
            ->get();

        // adm_master_import_history_versionsにはmng_master_release_version_idが複数存在する可能性があるので
        // その場合はcreated_atを比較して最新の更新日を取得する
        foreach ($admMasterImportHistories as $row) {
            $mngMasterReleaseVersionId = $row['mng_master_release_version_id'];
            if (!isset($result[$mngMasterReleaseVersionId])) {
                // $resultにデータがない場合は追加して次のループへ
                $result[$mngMasterReleaseVersionId] = $row['created_at'];
                continue;
            }
            // created_atを比較して最新の更新日を保持する
            $beforeCreatedAt = $result[$mngMasterReleaseVersionId];
            $result[$mngMasterReleaseVersionId] = max($beforeCreatedAt, $row['created_at']);
        }

        return $result;
    }

    /**
     * 対象リリースキーの最新バージョン(mng_master_release_version)のインポート日時を取得する
     *
     * @param array $releaseKeys
     * @return array
     */
    public function getLatestImportAtMapByReleaseKeys(array $releaseKeys): array
    {
        // 指定したMngMasterReleaseVersion.idを取得
        $mngMasterReleaseVersions = MngMasterReleaseVersion::query()
            ->select('id', 'release_key')
            ->whereIn('release_key', $releaseKeys)
            ->get(['id', 'release_key']);
        $mngMasterReleaseVersionIds = $mngMasterReleaseVersions->pluck('id');

        // mngMasterReleaseVersion.idを元にhistoryの作成日(インポート実行日)を取得
        $histories = AdmMasterImportHistoryVersion::query()
            ->select('mng_master_release_version_id', 'created_at')
            ->whereIn('mng_master_release_version_id', $mngMasterReleaseVersionIds)
            ->get(['mng_master_release_version_id', 'created_at']);

        // release_keyをキーにした、最新のmng_master_release_versionのidと作成日をもつマップを生成
        $resultMap = [];
        foreach ($mngMasterReleaseVersions as $mngMasterReleaseVersion) {
            $history = $histories
                ->filter(fn ($history) => $history['mng_master_release_version_id'] === $mngMasterReleaseVersion['id'])
                ->first();

            if (!isset($resultMap[$mngMasterReleaseVersion['release_key']])) {
                // 対象のリリースキーがセットされてなければセットして次のループへ
                $resultMap[$mngMasterReleaseVersion['release_key']] = [
                    'mng_master_release_version_id' => $mngMasterReleaseVersion['id'],
                    'import_at' => $history['created_at'],
                ];
                continue;
            }

            if ($resultMap[$mngMasterReleaseVersion['release_key']]['import_at'] < $history['created_at']) {
                // import_atより$history['created_at']が最新の場合はセットしなおす
                $resultMap[$mngMasterReleaseVersion['release_key']] =  [
                    'mng_master_release_version_id' => $mngMasterReleaseVersion['id'],
                    'import_at' => $history['created_at'],
                ];
            }
        }

        return $resultMap;
    }

    /**
     * マスターデータ取り込み実行後のデータ更新処理
     *
     * @param array $mngMasterReleaseKeys
     * @param string $gitRevision
     * @param array $masterDataHashMap
     * @param array $masterDataI18nHashMap
     * @param array $operationDataHashMap
     * @param array $operationDataI18nHashMap
     * @param array $masterSchemaVersions
     * @param array $serverDbHashMap
     * @param string $importAdmUserId
     * @param string $importSource
     * @return void
     */
    public function updateMasterRelease(
        array $mngMasterReleaseKeys,
        string $gitRevision,
        array $masterDataHashMap,
        array $masterDataI18nHashMap,
        array $operationDataHashMap,
        array $operationDataI18nHashMap,
        array $masterSchemaVersions,
        array $serverDbHashMap,
        array $dataHashMap,
        string $importAdmUserId,
        string $importSource,
    ): void {
        $mngMasterReleaseVersions = [];
        $targetVersionIdMap = [];

        $now = CarbonImmutable::now();
        // mng_master_release_versionsに登録
        foreach ($mngMasterReleaseKeys as $releaseKey) {
            $masterDataHash = $masterDataHashMap[$releaseKey];
            $operationDataHash = $operationDataHashMap[$releaseKey];
            $serverDbHash = $serverDbHashMap[$releaseKey];
            $dataHash = $dataHashMap[$releaseKey];

            $masterDataI18nHashList = $masterDataI18nHashMap[$releaseKey];
            $mstI18nJaHash = $masterDataI18nHashList[Language::Ja->value];
            $operationDataI18nHashList = $operationDataI18nHashMap[$releaseKey];
            $oprI18nJaHash = $operationDataI18nHashList[Language::Ja->value] ?? '';
            $opr = new MngMasterReleaseVersion();
            $versionId = $opr->newUniqueId();
            $dbName = config('app.env') . '_mst_' . "{$releaseKey}_{$serverDbHash}";

            // $masterSchemaVersionsに対象がなければ空文字としているが、本来なら想定外の挙動
            // だが、$masterSchemaVersionの重要度はそこまで高くないので、一旦空文字としている
            $masterSchemaVersion = $masterSchemaVersions[$dbName] ?? '';
            $mngMasterReleaseVersions[] = [
                'id' => $versionId,
                'release_key' => $releaseKey,
                'git_revision' => $gitRevision,
                'master_schema_version' => $masterSchemaVersion,
                'data_hash' => $dataHash,
                'server_db_hash' => $serverDbHash,
                'client_mst_data_hash' => $masterDataHash,
                'client_mst_data_i18n_ja_hash' => $mstI18nJaHash,
                'client_mst_data_i18n_en_hash' => '',
                'client_mst_data_i18n_zh_hash' => '',
                'client_opr_data_hash' => $operationDataHash,
                'client_opr_data_i18n_ja_hash' => $oprI18nJaHash,
                'client_opr_data_i18n_en_hash' => '',
                'client_opr_data_i18n_zh_hash' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $targetVersionIdMap[$releaseKey] = $versionId;
        }
        MngMasterReleaseVersion::query()
            ->insert($mngMasterReleaseVersions);

        // adm_master_import_historiesを登録
        $admMasterImportHistory = AdmMasterImportHistory::query()
            ->create([
                'git_revision' => $gitRevision,
                'import_adm_user_id' => $importAdmUserId,
                'import_source' => $importSource,
            ]);

        foreach ($targetVersionIdMap as $releaseKey => $versionId) {
            // mng_master_releasesの対象release_keyのtarget_release_version_idを更新
            MngMasterRelease::query()
                ->where('release_key', $releaseKey)
                ->update(['target_release_version_id' => $versionId]);

            // adm_master_import_history_versionsを登録
            AdmMasterImportHistoryVersion::query()
                ->create([
                    'adm_master_import_history_id' => $admMasterImportHistory->id,
                    'mng_master_release_version_id' => $versionId,
                ]);
        }
    }

    /**
     * mng_master_releasesとmng_master_release_versionsから対象idのデータを削除する
     *
     * @param MngMasterRelease $mngMasterRelease
     * @return void
     */
    public function deleteMasterRelease(MngMasterRelease $mngMasterRelease): void
    {
        MngMasterRelease::query()
            ->where('id', $mngMasterRelease->id)
            ->delete();

        /** @var MngMasterReleaseVersion|null $mngMasterReleaseVersion */
        $mngMasterReleaseVersion = $mngMasterRelease->mngMasterReleaseVersion;
        if (!is_null($mngMasterReleaseVersion)) {
            MngMasterReleaseVersion::query()
                ->where('id', $mngMasterReleaseVersion->id)
                ->delete();
        }
    }

    /**
     * 対象idのrelease_key以前のenabledを更新する
     *
     * @param string $mngMasterReleaseId
     * @return void
     * @throws \Exception
     */
    public function releasedMngMasterReleasesById(string $mngMasterReleaseId): void
    {
        /** @var MngMasterRelease|null $targetMngMasterRelease */
        $targetMngMasterRelease = MngMasterRelease::query()
            ->where('id', $mngMasterReleaseId)
            ->first();

        if (is_null($targetMngMasterRelease)) {
            throw new \Exception("not found mng_master_releases id:{$mngMasterReleaseId}");
        }

        // 対象とするreleaseKey以前のMngMasterReleaseのステータスを更新する
        $releaseKey = $targetMngMasterRelease->release_key;
        MngMasterRelease::query()
            ->where('release_key', '<=', $releaseKey)
            ->whereNotNull('target_release_version_id')
            ->update(['enabled' => 1]);
    }

    /**
     * インポート元環境情報を取得
     *
     * @return array
     */
    public function getImportEnvironment(): array
    {
        // アセットのインポート元と同じ情報を取得
        return config('wp_master_asset_release_admin.importable_from_environment_list');
    }
    
    /**
     * @param string $environment
     * @param array $releaseKeys
     * @return Collection
     */
    public function getEffectiveMasterReleaseListFromEnvironment(string $environment, array $releaseKeys): Collection
    {
        // 指定したインポート元環境のAPIを叩いてrelease_keyの情報を取得する
        $domainList = config('wp_master_asset_release_admin.admin_api_domain');
        $domain = $domainList[$environment];
        
        // 指定したリリースキーをクエリパラメータ化して実行する(空配列の場合はクエリパラメータ部分は空文字になる)
        $queryString = http_build_query(['releaseKeys' => $releaseKeys]);
        $endpoint = 'get-master-release-data?' . $queryString;
        $response = $this->sendApiService->sendApiRequest($domain, $endpoint);
        return collect($response);
    }

    /**
     * 指定したリリースキーのリリース情報を取得する
     *
     * @param array $releaseKeys
     * @return Collection
     */
    public function getMngMasterReleasesByReleaseKeys(array $releaseKeys): Collection
    {
        return MngMasterRelease::query()
            ->whereIn('release_key', $releaseKeys)
            ->whereNotNull('target_release_version_id')
            ->get();
    }
}
