<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Ramsey\Uuid\Uuid;
use WonderPlanet\Domain\Admin\Operators\S3Operator;
use WonderPlanet\Domain\Admin\Services\SendApiService;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\MasterAssetRelease\Constants\AssetData;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Enums\ReleaseStatus;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmAssetImportHistory;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetReleaseVersion;

/**
 * アセット管理ツールのサービスクラス
 */
class MngAssetReleaseService
{
    /**
     * @var S3Operator
     */
    private S3Operator $s3Operator;

    private SendApiService $sendApiService;

    public const PLATFORM_S3_DIR_LIST = [
        PlatformConstant::PLATFORM_IOS => 'ios',
        PlatformConstant::PLATFORM_ANDROID => 'android',
    ];

    public function __construct() {
        $this->s3Operator = app()->make(S3Operator::class);
        $this->sendApiService = app()->make(SendApiService::class);
    }

    /**
     * 全プラットフォームの最も最新の配信中のMngAssetReleaseを取得
     *
     * @return Collection
     */
    public function getAllPlatformLatestReleasedMngAssetReleases(): Collection
    {
        $iosQuery = MngAssetRelease::query()
            ->where('enabled', 1)
            ->where('platform', PlatformConstant::PLATFORM_IOS)
            ->whereNotNull('target_release_version_id')
            ->orderBy('release_key', 'desc')
            ->limit(1);
        $androidQuery = MngAssetRelease::query()
            ->where('enabled', 1)
            ->where('platform', PlatformConstant::PLATFORM_ANDROID)
            ->whereNotNull('target_release_version_id')
            ->orderBy('release_key', 'desc')
            ->limit(1);
        
        return $iosQuery->union($androidQuery)->get();
    }

    /**
     * 全プラットフォームの配信中のMngAssetReleaseを取得
     *
     * @return Collection
     */
    public function getAllPlatformApplyMngAssetReleases(): Collection
    {
        $iosQuery = MngAssetRelease::query()
            ->where('enabled', 1)
            ->where('platform', PlatformConstant::PLATFORM_IOS)
            ->whereNotNull('target_release_version_id')
            ->orderBy('release_key', 'desc')
            ->limit(AssetData::ASSET_RELEASE_APPLY_LIMIT);
        $androidQuery = MngAssetRelease::query()
            ->where('enabled', 1)
            ->where('platform', PlatformConstant::PLATFORM_ANDROID)
            ->whereNotNull('target_release_version_id')
            ->orderBy('release_key', 'desc')
            ->limit(AssetData::ASSET_RELEASE_APPLY_LIMIT);

        return $iosQuery->union($androidQuery)->get();
    }

    /**
     * 配信中/配信準備中のMngAssetReleaseを取得する
     *
     * @return Collection
     */
    public function getMngAssetReleasesByApplyOrPending(): Collection
    {
        // 全platform中の配信中ステータスのrelease_keyのうち、一番古いものを取得(なければ0)
        $latestReleasedMngAssetReleaseCollection = $this->getAllPlatformApplyMngAssetReleases();
        $latestAssetReleaseKey = $latestReleasedMngAssetReleaseCollection->isEmpty()
            ? 0
            : $latestReleasedMngAssetReleaseCollection
                ->sortBy('release_key')
                ->pluck('release_key')
                ->first();

        return MngAssetRelease::query()
            ->whereNot(function ($query) use ($latestAssetReleaseKey) {
                // 「配信中」または「配信準備中」データを取得
                // 「配信終了」以外のデータを取得する条件としている
                $query
                    ->where('enabled', 1)
                    ->whereNotNull('target_release_version_id')
                    // 配信中の最古のrelease_keyよりも古いrelease_keyを指定
                    ->where('release_key', '<', $latestAssetReleaseKey);
            })
            ->orderBy('release_key', 'desc')
            ->get();
    }

    /**
     * adm_asset_import_historiesからlast import at(created_at)のマップを取得する
     *
     * @return array<string, array>
     */
    public function getLastImportAtMap(): array
    {
        return AdmAssetImportHistory::all()->pluck('created_at', 'mng_asset_release_version_id')->toArray();
    }

    /**
     * mng_asset_releasesとmng_asset_release_versionsから対象idのデータを削除する
     *
     * @param MngAssetRelease $mngAssetRelease
     * @return void
     */
    public function deleteAssetRelease(MngAssetRelease $mngAssetRelease): void
    {
        MngAssetRelease::query()
            ->where('id', $mngAssetRelease->id)
            ->delete();

        $mngAssetReleaseVersionCollection = $mngAssetRelease->mngAssetReleaseVersion;
        foreach ($mngAssetReleaseVersionCollection as $mngAssetReleaseVersion) {
            MngAssetReleaseVersion::query()
                ->where('id', $mngAssetReleaseVersion->id)
                ->delete();
        }
    }

    /**
     * 対象idのrelease_key以前のenabledを更新する
     *
     * @param string $mngAssetReleaseId
     * @return void
     * @throws \Exception
     */
    public function releasedMngAssetReleasesById(string $mngAssetReleaseId): void
    {
        /** @var MngAssetRelease|null $mngAssetRelease */
        $mngAssetRelease = MngAssetRelease::query()
            ->where('id', $mngAssetReleaseId)
            ->first();

        if (is_null($mngAssetRelease)) {
            throw new \Exception("not found mng_asset_releases id:{$mngAssetReleaseId}");
        }

        // 対象とするreleaseKey以前のMngAssetReleaseのステータスを更新する
        $releaseKey = $mngAssetRelease->release_key;
        $platform = $mngAssetRelease->platform;
        MngAssetRelease::query()
            ->where('release_key', '<=', $releaseKey)
            ->whereNotNull('target_release_version_id')
            ->where('platform', $platform)
            ->update(['enabled' => 1]);
    }

    /**
     * 最新のリリース済みのリリースキーを取得する
     *
     * @param int $platform
     * @return int|null
     */
    public function getLatestReleaseKey(int $platform): ?int
    {
        $mngAssetRelease = MngAssetRelease::query()
            ->where([
                'platform' => $platform,
                'enabled' => 1
            ])
            ->whereNotNull('target_release_version_id')
            ->orderBy('release_key', 'desc')
            ->limit(1)
            ->first();

        if (is_null($mngAssetRelease)) {
            return null;
        }

        return $mngAssetRelease->release_key;
    }

    /**
     * 配信中ステータスのうち一番古いMngAssetReleaseKeyを取得
     *
     * @param int $platform
     * @return int
     */
    public function getOldestApplyMngAssetReleaseKey(int $platform): int
    {
        $oldestApplyRelease = $this->getAllPlatformApplyMngAssetReleases()
            ->filter(fn (MngAssetRelease $assetRelease) => $assetRelease->platform === $platform)
            ->sortBy('release_key')
            ->first();
        
        if (is_null($oldestApplyRelease)) {
            return 0;
        }
        
        return $oldestApplyRelease->release_key;
    }

    /**
     * アセットの配信ステータスを取得する
     *
     * @param MngAssetRelease $mngAssetRelease
     * @param int|null $latestReleaseKey
     *
     * @return ReleaseStatus
     */
    public function getReleaseStatus(
        MngAssetRelease $mngAssetRelease,
        int|null $latestReleaseKey,
        Collection $allPlatformApplyMngAssetReleases,
    ): ReleaseStatus {
        // デフォルトで「配信終了」を設定
        $status = ReleaseStatus::RELEASE_STATUS_END;

        $filteredApplyMngAssetRelease = $allPlatformApplyMngAssetReleases
            ->filter(fn (MngAssetRelease $assetRelease) => $assetRelease->id === $mngAssetRelease->id);
        if ($filteredApplyMngAssetRelease->isNotEmpty()) {
            // 配信中ステータスと一致する場合は配信中とする
            $status = ReleaseStatus::RELEASE_STATUS_APPLYING;
        }
        
        if (! $mngAssetRelease->enabled || is_null($mngAssetRelease->target_release_version_id)) {
            // enabledフラグがfalse または target_release_version_idが設定されていない場合
            // 最新リリース済みデータがnullの場合は$recordのデータが最新なので「配信準備中」とする
            // 最新リリース済みデータがある場合、$recordのreleaseKeyの方がより最新なら「配信準備中」とする
            if (is_null($latestReleaseKey) || ($mngAssetRelease->release_key > $latestReleaseKey)) {
                $status = ReleaseStatus::RELEASE_STATUS_PENDING;
            }
        }

        return $status;
    }

    /**
     * アセットの配信ステータスが適用中もしくは準備中のレコードを取得する
     * $latestReleaseKey がnullであれば、「準備中」だけ返却されるが、nullでなければ「配信中/準備中」ともに返却される
     * 
     * @param int $platform
     * @param int|null $latestReleaseKey
     *
     * @return Collection
     */
    public function getEffectiveAssetReleaseList(int $platform, ?int $latestReleaseKey): Collection
    {
        $allPlatformApplyMngAssetReleases = $this->getAllPlatformApplyMngAssetReleases();
        return MngAssetRelease::query()
            ->where('platform', $platform)
            ->orderBy('release_key', 'desc')
            ->get()
            ->filter(function (MngAssetRelease $mngAssetRelease) use ($latestReleaseKey, $allPlatformApplyMngAssetReleases) {
                // 配信ステータスが適用中もしくは準備中のレコードのみ返す
                $status = $this->getReleaseStatus($mngAssetRelease, $latestReleaseKey, $allPlatformApplyMngAssetReleases);
                return $status === ReleaseStatus::RELEASE_STATUS_APPLYING || $status === ReleaseStatus::RELEASE_STATUS_PENDING;
            });
    }

    /**
     * 指定したディレクトリ内の全アセットファイルとサイズを取得
     * 1階層のみ対応
     *
     * @param string $config
     * @param string $directory
     *
     * @return Collection
     */
    public function getAllAssetFiles(string $config, string $directory): Collection
    {
        $result = [];
        $allFiles = $this->s3Operator->getAllFilesAndSize($config, $directory);
        foreach ($allFiles as $fileData) {
            // assetbundles/[platform]/[hash値]/....
            // 上記パスの部分を削除
            $explode = explode('/', $fileData['file']);
            array_splice($explode, 0, 3);
            $fileName = implode('/', $explode);
            $result[] = [
                'file' => $fileName,
                'size' => $fileData['size'],
                'size_format' => Number::fileSize($fileData['size'], 2),
            ];
        }

        // ファイル名でキーを作る
        return collect($result)->mapWithKeys(function ($item) {
            return [$item['file'] => $item];
        });
    }

    /**
     * インポート元環境と操作中環境のs3接続情報取得
     *
     * @param string $environment
     * @param string $fromEnvironment
     *
     * @return array
     */
    public function getAssetConfigNameBoth(string $environment, string $fromEnvironment): array
    {
        $prefixKey = "s3_asset_";
        return [
            // インポート元環境
            'input' => $prefixKey . $fromEnvironment,
            // 操作中環境
            'output' => $prefixKey . $environment,
        ];
    }

    /**
     * インポート元環境の有効リリースキー一覧取得
     *
     * @param string $environment
     * @param int $platform
     *
     * @return Collection
     */
    public function getEffectiveReleaseKeyListFromEnvironment(string $environment, int $platform): Collection
    {
        // 指定したインポート元環境のAPIを叩いてrelease_keyの情報を取得する
        $domainList = config('wp_master_asset_release_admin.admin_api_domain');
        $domain = $domainList[$environment];
        $endpoint = 'get-asset-release-data/' . $platform;
        $response = $this->sendApiService->sendApiRequest($domain, $endpoint);
        return collect($response);
    }

    /**
     * アセットが保管されているディレクトリパスを作成
     * @param string $environment
     * @param int $platform
     * @param int $releaseKey
     * @return Collection
     */
    public function createAssetFileDirectoryPathAndGetAssetInfo(
        string $environment,
        int $platform,
        int $releaseKey
    ): Collection {
        if ($environment === env('APP_ENV')) {
            // 自環境の場合はAPIを経由せずに各種データを取得する
            $targetEnvironmentAssetData = $this->getAssetReleaseAndReleaseVersionByReleaseKey($platform, $releaseKey);
            if (is_null($targetEnvironmentAssetData['mng_asset_release_version'])) {
                // 空の場合は配信中の最新リリースキーとの差分を表示するためその情報を取得する
                $latestReleaseKey = $this->getLatestReleaseKey($platform);
                if (!is_null($latestReleaseKey)) {
                    // 一番最初に、「配信中」のデータがない時もあるので、データがあるときだけ「配信中」のデータを引っ張ってきて差分比較する
                    $targetEnvironmentAssetData = $this->getAssetReleaseAndReleaseVersionByReleaseKey($platform, $latestReleaseKey);
                }
            }
        } else {
            // 自環境以外の場合はAPI経由で各種データを取得する
            $targetEnvironmentAssetData = $this->getMngAssetResourceInfoByApi($environment, $platform, $releaseKey);
            if (!array_key_exists('asset_release_info', $targetEnvironmentAssetData->toArray())) {
                // アセットリリース情報がない場合はインポートするものがないのでエラーを返す
                throw new \Exception("インポート元環境にアセットバージョン情報があるか確認してください。release_key: " . $releaseKey);
            }
        }
        $assetInfo = $targetEnvironmentAssetData['asset_release_info'];
        $platformDir = self::PLATFORM_S3_DIR_LIST[$platform];
        $path = $assetInfo['catalog_hash'] === '' ? '' : '/assetbundles/' . $platformDir . '/' . $assetInfo['catalog_hash'];
        $result = [
            'asset_info' => $assetInfo,
            'path' => $path,
            'release_version' => $targetEnvironmentAssetData['mng_asset_release_version'] ?? null,
        ];
        return collect($result);
    }

    /**
     *
     * 対象環境のアセット情報(mng_asset_releases, mng_asset_release_versions)を取得
     * @param int $platform
     * @param int $releaseKey
     * @return Collection
     */
    public function getAssetReleaseAndReleaseVersionByReleaseKey(int $platform, int $releaseKey): Collection
    {
        $latestReleaseKey = $this->getLatestReleaseKey($platform);
        $mngAssetRelease = $this->getMngAssetReleaseByReleaseKey($platform, $releaseKey, $latestReleaseKey);
        if (is_null($mngAssetRelease)) {
            // 指定したrelease_keyがないorステータスが配信中or準備中でないものを指定された場合は空配列を返す
            return collect();
        }
        // 配信中のリリース情報を取得
        $allPlatformApplyMngAssetReleases = $this->getAllPlatformApplyMngAssetReleases();
        // 配信ステータス情報を作成する
        $mngAssetReleaseStatus = $this->getReleaseStatus($mngAssetRelease, $latestReleaseKey, $allPlatformApplyMngAssetReleases);
        // 指定したmng_asset_release_versions情報を取得する
        $mngAssetReleaseVersion = $this->getAssetReleaseVersionById($mngAssetRelease->target_release_version_id);
        $assetInfo = [
            'platform' => $mngAssetRelease['platform'],
            'release_key' => $mngAssetRelease['release_key'],
            'status' => $mngAssetReleaseStatus->value,
            'git_revision' => $mngAssetReleaseVersion['git_revision'] ?? '',
            'catalog_hash' => $mngAssetReleaseVersion['catalog_hash'] ?? '',
            'description' => $mngAssetRelease['description']
        ];
        $result = [
            'asset_release_info' => $assetInfo,
            'mng_asset_release_version' => $mngAssetReleaseVersion
        ];
        return collect($result);
    }

    /**
     * 対象環境のアセット情報(mng_asset_releases, mng_asset_release_versions)をAPI経由で取得
     * @param string $environment
     * @param int $platform
     *
     * @return Collection
     */
    private function getMngAssetResourceInfoByApi(string $environment, int $platform, int $releaseKey): Collection
    {
        // 指定したインポート元環境のAPIを叩いてrelease_keyの情報を取得する
        $domainList = config('wp_master_asset_release_admin.admin_api_domain');
        $domain = $domainList[$environment];
        $endpoint = 'get-asset-release-data/' . $platform . '/' . $releaseKey;
        $response = $this->sendApiService->sendApiRequest($domain, $endpoint);
        return collect($response);
    }

    /**
     * インポート元環境情報を取得
     * @return array
     */
    public function getImportEnvironment(): array
    {
        return config('wp_master_asset_release_admin.importable_from_environment_list');
    }

    /**
     * アセット配信管理画面で表示するJenkinsのURLリストを取得
     * @return array
     */
    public function getAssetCreateJenkinsUrlList(): array
    {
        return config('wp_master_asset_release_admin.jenkins_url_list');
    }

    /**
     * 指定したrelease_keyかつ配信ステータスが適用中もしくは準備中のレコードを取得して返す
     * @param int $platform
     * @param int $releaseKey
     * @param int|null $latestReleaseKey
     * @return MngAssetRelease|null
     */
    public function getMngAssetReleaseByReleaseKey(int $platform, int $releaseKey, ?int $latestReleaseKey): ?MngAssetRelease
    {
        // アセットの配信ステータスが適用中もしくは準備中のレコードを取得する
        $mngAssetReleases = $this->getEffectiveAssetReleaseList($platform, $latestReleaseKey);
        // 指定されたrelease_keyの情報のみを返す
        $result = null;
        foreach ($mngAssetReleases as $mngAssetRelease) {
            if ($mngAssetRelease->release_key !== $releaseKey) {
                continue;
            }
            // 指定されたrelease_key情報ならresultにセットして返す
            $result = $mngAssetRelease;
            break;
        }

        return $result;
    }

    /**
     * 指定したidのmng_asset_release_versionを取得
     * @param string|null $id
     * @return MngAssetReleaseVersion|null
     */
    public function getAssetReleaseVersionById(?string $id): ?MngAssetReleaseVersion
    {
        if (is_null($id)) {
            return null;
        }
        return MngAssetReleaseVersion::query()->where(['id' => $id])->get()->first();
    }

    /**
     * インポート元環境からアセットを自環境のS3バケットにコピーする
     * @param string $fromConfig
     * @param string $toConfig
     * @param int $platform
     * @param string $catalogHash
     * @return void
     */
    public function assetImport(string $fromConfig, string $toConfig, int $platform, string $catalogHash): void
    {
        $platformDir = self::PLATFORM_S3_DIR_LIST[$platform];
        $path = '/assetbundles/' . $platformDir . '/' . $catalogHash;
        $this->s3Operator->copyAsset($fromConfig, $toConfig, $path);
    }

    /**
     * インポート元環境のmng_asset_release_versionsデータを自環境のmng_asset_release_versionsへinsertし、
     * 自環境のmng_asset_releasesのtarget_release_version_idを更新する
     * @param int $releaseKey
     * @param int $platform
     * @param Collection $fromEnvironmentReleaseVersion
     * @return void
     */
    public function insertReleaseVersionAndUpdateTargetId(
        int $releaseKey,
        int $platform,
        Collection $fromEnvironmentReleaseVersion
    ): void {
        // mng_asset_release_versionsをinsert
        $targetId = $this->insertReleaseVersion($releaseKey, $fromEnvironmentReleaseVersion);

        // mng_asset_releasesのtarget_release_version_idを更新
        $latestReleaseKey = $this->getLatestReleaseKey($platform);
        $mngAssetRelease = $this->getMngAssetReleaseByReleaseKey($platform, $releaseKey, $latestReleaseKey);
        $mngAssetRelease->target_release_version_id = $targetId;
        $saveResult = $mngAssetRelease->save();
        if (!$saveResult) {
            // update失敗時はエラーを返す
            throw new \Exception('mng_asset_releasesへのupdateが失敗しました。');
        }
    }

    /**
     * mng_asset_release_versionsへinsertを実行する
     * @param int $releaseKey
     * @param Collection $releaseVersion
     * @return string
     */
    public function insertReleaseVersion(int $releaseKey, Collection $releaseVersion): string
    {
        $now = CarbonImmutable::now();
        $targetId = (string)Uuid::uuid4();
        $input = [
            'id' => $targetId,
            'release_key' => $releaseKey,
            'git_revision' => $releaseVersion['git_revision'],
            'git_branch' => $releaseVersion['git_branch'],
            'catalog_hash' => $releaseVersion['catalog_hash'],
            'platform' => $releaseVersion['platform'],
            'build_client_version' => $releaseVersion['build_client_version'],
            'asset_total_byte_size' => $releaseVersion['asset_total_byte_size'],
            'catalog_byte_size' => $releaseVersion['catalog_byte_size'],
            'catalog_file_name' => $releaseVersion['catalog_file_name'],
            'catalog_hash_file_name' => $releaseVersion['catalog_hash_file_name'],
            'created_at' => $now,
            'updated_at' => $now
        ];
        // mng_asset_release_versionsを登録
        $insertResult = MngAssetReleaseVersion::query()->insert($input);
        if (!$insertResult) {
            // insert失敗時はエラーを返す
            throw new \Exception('mng_asset_release_versionsへのinsertが失敗しました。');
        }
        return $targetId;
    }
}
