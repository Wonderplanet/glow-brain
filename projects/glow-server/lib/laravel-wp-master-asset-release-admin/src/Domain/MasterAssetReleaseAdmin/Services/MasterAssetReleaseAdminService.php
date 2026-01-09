<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use WonderPlanet\Domain\Admin\Trait\DatabaseTransactionTrait;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\Common\Utils\DBUtility;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm\AdmAssetImportHistory;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;

readonly class MasterAssetReleaseAdminService
{
    use DatabaseTransactionTrait;
    
    // adm_asset_import_histories登録用
    const IMPORT_USER_FOR_REGISTER_ASSET = "register-asset-api";
    const IMPORT_RESOURCE_FOR_REGISTER_ASSET = "register-asset";
    
    /**
     * アセットの登録を行う
     * jenkinsビルドサーバーから呼ばれる
     * @param Request $request
     * @return JsonResponse
     */
    public function registerAsset(Request $request): JsonResponse
    {
        // 本番相当の環境では利用不可
        if (!config('app.debug') || app()->isProduction()) {
            return response()->json(['message' => 'invalid operation'], 400);
        }
        
        // 入力値のバリデーション
        $validated = Validator::make($request->all(), $this->getValidationRules())->validate();
        
        $this->transaction(
            function () use ($validated) {
                try {
                    $mngAssetReleaseVersions = $validated['mng_asset_release_versions'];
                    
                    $platform = array_search($mngAssetReleaseVersions['platform'], PlatformConstant::PLATFORM_STRING_LIST);
                    
                    $releaseKey = 0;
                    if ($mngAssetReleaseVersions['release_key']) {
                        $releaseKey = $mngAssetReleaseVersions['release_key'];
                    }
                    
                    /** @var MngAssetReleaseService $service */
                    $service = app()->make(MngAssetReleaseService::class);
                    
                    // mng_asset_release_versionsをinsertする
                    $releaseVersion = [
                        'release_key' => $releaseKey,
                        'git_revision' => $mngAssetReleaseVersions['git_revision'],
                        'git_branch' => $mngAssetReleaseVersions['git_branch'],
                        'catalog_hash' => $mngAssetReleaseVersions['catalog_hash'],
                        'platform' => $platform,
                        'build_client_version' => $mngAssetReleaseVersions['build_client_version'],
                        'asset_total_byte_size' => $mngAssetReleaseVersions['asset_total_byte_size'],
                        'catalog_byte_size' => $mngAssetReleaseVersions['catalog_byte_size'],
                        'catalog_file_name' => $mngAssetReleaseVersions['catalog_file_name'],
                        'catalog_hash_file_name' => $mngAssetReleaseVersions['catalog_hash_file_name'],
                    ];
                    $targetId = $service->insertReleaseVersion($releaseKey, collect($releaseVersion));
                    
                    // mng_asset_releases情報を取得
                    /** @var MngAssetRelease $mngAssetRelease */
                    $mngAssetRelease = MngAssetRelease::query()
                        ->where('release_key', $releaseKey)
                        ->where('platform', $platform)
                        ->get()
                        ->first();
                    if (is_null($mngAssetRelease)) {
                        // mng_asset_releasesがない場合は配信準備中でcreateを実行
                        $mngAssetRelease = new MngAssetRelease();
                        $mngAssetRelease->release_key = $releaseKey;
                        $mngAssetRelease->platform = $platform;
                        $mngAssetRelease->enabled = false;
                        $mngAssetRelease->target_release_version_id = $targetId;
                        $mngAssetRelease->description = null;
                        $mngAssetRelease->save();
                    } else {
                        // mng_asset_releasesがある場合
                        // 配信ステータス情報を作成する
                        // target_release_version_idの更新を行う
                        $mngAssetRelease->target_release_version_id = $targetId;
                        $mngAssetRelease->save();
                    }
                    
                    // adm_asset_import_historiesにインポートログを挿入する
                    $admAssetImportHistory = new AdmAssetImportHistory();
                    $admAssetImportHistory->mng_asset_release_version_id = $targetId;
                    $admAssetImportHistory->import_adm_user_id = self::IMPORT_USER_FOR_REGISTER_ASSET;
                    $admAssetImportHistory->import_source = self::IMPORT_RESOURCE_FOR_REGISTER_ASSET;
                    $admAssetImportHistory->save();
                } catch (\Exception $e) {
                    return response()->json(['message' => $e->getMessage()], 500);
                }
            }, [DBUtility::getMngConnName(), DBUtility::getAdminConnName()]
        );
        return response()->json();
    }
    
    /**
     * registerAsset用
     * バリデーションルール一覧取得
     *
     * @return array
     */
    private function getValidationRules(): array
    {
        return [
            'mng_asset_release_versions' => [
                'required',
                'array',
            ],
            'mng_asset_release_versions.release_key' => [
                'required',
                'integer',
            ],
            'mng_asset_release_versions.git_revision' => [
                'required',
                'string',
            ],
            'mng_asset_release_versions.git_branch' => [
                'required',
                'string',
            ],
            'mng_asset_release_versions.catalog_hash' => [
                'required',
                'string',
            ],
            'mng_asset_release_versions.platform' => [
                'required',
                'string',
            ],
            'mng_asset_release_versions.build_client_version' => [
                'required',
                'string',
            ],
            'mng_asset_release_versions.asset_total_byte_size' => [
                'required',
                'integer'
            ],
            'mng_asset_release_versions.catalog_byte_size' => [
                'required',
                'integer'
            ],
            'mng_asset_release_versions.catalog_file_name' => [
                'required',
                'string'
            ],
            'mng_asset_release_versions.catalog_hash_file_name' => [
                'required',
                'string'
            ],
        ];
    }
    
    /**
     * 指定プラットフォームのアセットリリースキー一覧(配信ステータスが適用中もしくは準備中)を取得し返す
     * 他環境からアセットインポートを行う際に呼ばれるAPI
     * @param int $platform
     * @return JsonResponse
     */
    public function getEffectiveAssetReleases(int $platform): JsonResponse
    {
        // 入力値のバリデーション
        $param = [
            'platform' => $platform,
        ];
        $validationRules = [
            'platform' => [
                'required',
                'integer'
            ],
        ];
        $validated = Validator::make($param, $validationRules)->validate();
        $platform = $validated['platform'];

        /** @var MngAssetReleaseService $service */
        $service = app()->make(MngAssetReleaseService::class);

        // 配信ステータスが適用中もしくは準備中のレコードを取得し返す
        $result = $service->getMngAssetReleasesByApplyOrPending()
            ->filter(fn (MngAssetRelease $row) => $row->platform === $platform);
        
        $response = [];
        foreach ($result as $item) {
            $response[] = [
                'release_key' => $item->release_key,
                'description' => $item->description,
                'target_release_version_id' => $item->target_release_version_id,
            ];
        }
        return response()->json($response);
    }
    
    /**
     * 指定されたplatformとrelease_keyのmng_asset_releases, mng_asset_release_versions情報を取得して返す
     * 他環境からアセットインポートを行う際に呼ばれるAPI
     * @param int $platform
     * @param int|null $releaseKey
     * @return JsonResponse
     */
    public function getAssetReleaseData(int $platform, int|null $releaseKey): JsonResponse
    {
        // 入力値のバリデーション
        $param = [
            'platform' => $platform,
            'release_key' => $releaseKey,
        ];
        $validationRules = [
            'platform' => [
                'required',
                'integer'
            ],
            'release_key' => [
                'required',
                'integer',
            ],
        ];
        
        $validated = Validator::make($param, $validationRules)->validate();
        $platform = $validated['platform'];
        $releaseKey = $validated['release_key'];
        
        /** @var MngAssetReleaseService $service */
        $service = app()->make(MngAssetReleaseService::class);
        $result = $service->getAssetReleaseAndReleaseVersionByReleaseKey($platform, $releaseKey);
        if ($result->isEmpty()) {
            // 指定したrelease_keyがないorステータスが配信中or準備中でないものを指定された場合
            // その場合はconfirm画面を表示できないためエラーコードを返すようにする
            return response()->json([], 500);
        }
        return response()->json($result);
    }

    /**
     * インポート先で指定されたreleaseKeyのデータを取得する
     * マスターデータ環境間インポートを行う際に呼ばれるAPI
     *
     * @param array $releaseKeys
     * @return JsonResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getMasterReleaseData(array $releaseKeys): JsonResponse
    {
        /** @var MngMasterReleaseService $mngMasterReleaseService */
        $mngMasterReleaseService = app()->make(MngMasterReleaseService::class);
        $mngMasterReleaseCollection = $mngMasterReleaseService->getMngMasterReleasesByReleaseKeys($releaseKeys);
        
        if ($mngMasterReleaseCollection->isEmpty()) {
            // 指定されたリリースキーが存在しない場合は空で返す
            return response()->json();
        }
        
        $applyMngMasterReleaseCollection = $mngMasterReleaseService->getMngMasterReleasesByApply();

        // 対象リリースキーごとの最新のインポート日時情報を取得
        $releaseKeys = $mngMasterReleaseCollection
            ->pluck('release_key')
            ->toArray();
        $importAtMap = $mngMasterReleaseService->getLatestImportAtMapByReleaseKeys($releaseKeys);

        $response = $mngMasterReleaseCollection
            ->map(function (MngMasterRelease $mng) use ($importAtMap, $applyMngMasterReleaseCollection) {
                // レスポンス用に整形
                /** @var MngMasterReleaseVersion $version */
                $version = $mng->mngMasterReleaseVersion;

                $isLatestVersion = true;
                if ($importAtMap[$mng->release_key]['mng_master_release_version_id'] !== $version['id']) {
                    // 最新バージョンのidと一致してなければ、target_release_version_idが古い
                    $isLatestVersion = false;
                }

                // 配信中のリリース情報と一致するか
                $isApplyMngRelease = $applyMngMasterReleaseCollection
                    ->filter(fn (MngMasterRelease $apply) => $apply->release_key === $mng->release_key)
                    ->isNotEmpty();
                // enabledがtrueかつ現在配信中のリリース情報でなければ配信終了とみなす
                $isEndRelease = $mng->enabled && !$isApplyMngRelease;

                return [
                    'release_key' => $mng->release_key,
                    'description' => $mng->description,
                    'enabled' => $mng->enabled,
                    'is_latest_version' => $isLatestVersion,
                    'is_end_release' => $isEndRelease,
                    'mng_master_release_versions' => [
                        'release_key' => $version->release_key,
                        'git_revision' => $version->git_revision,
                        'master_schema_version' => $version->master_schema_version,
                        'data_hash' => $version->data_hash,
                        'server_db_hash' => $version->server_db_hash,
                        'client_mst_data_hash' => $version->client_mst_data_hash,
                        'client_mst_data_i18n_ja_hash' => $version->client_mst_data_i18n_ja_hash,
                        'client_mst_data_i18n_en_hash' => $version->client_mst_data_i18n_en_hash,
                        'client_mst_data_i18n_zh_hash' => $version->client_mst_data_i18n_zh_hash,
                        'client_opr_data_hash' => $version->client_opr_data_hash,
                        'client_opr_data_i18n_ja_hash' => $version->client_opr_data_i18n_ja_hash,
                        'client_opr_data_i18n_en_hash' => $version->client_opr_data_i18n_en_hash,
                        'client_opr_data_i18n_zh_hash' => $version->client_opr_data_i18n_zh_hash,
                    ],
                ];
            })->toArray();

        return response()->json($response);
    }
}
