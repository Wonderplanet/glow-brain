<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Pages\MngMasterReleases;

use App\Traits\MngCacheDeleteTrait;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Admin\Trait\DatabaseTransactionTrait;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\Common\Utils\DBUtility;
use WonderPlanet\Domain\MasterAssetRelease\Constants\AssetData;
use WonderPlanet\Domain\MasterAssetRelease\Constants\MasterData;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetReleaseVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngAssetReleaseService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngMasterReleaseService;

/**
 * マスターデータインポートv2管理ツールページクラス
 * マスターデータ/アセットデータのリリースを処理する
 */
class MngMasterAndAssetReleaseUpdate extends Page
{
    use InteractsWithFormActions;
    use DatabaseTransactionTrait;
    use MngCacheDeleteTrait;

    protected static string $view = 'view-master-asset-admin::filament.pages.mng-master-releases.mng-master-and-asset-release-update';
    protected static ?string $slug = 'mng-master-and-asset-release/update'; // URLを別に付与

    protected static ?int $navigationSort = -994;
    protected static ?string $navigationIcon = 'heroicon-m-arrow-right-on-rectangle';
    protected static ?string $navigationGroup = 'v2 マスター・アセット管理';
    protected static ?string $navigationLabel = 'マスタアセットリリース';
    protected static ?string $title = 'マスターデータ/アセットリリース';
    private string $confirmModalView = 'view-master-asset-admin::filament.pages.mng-master-releases.execute-confirm-modal';

    /** フォームで設定するパラメータ  */
    public array $checkMasterReleaseIds = [];
    public array $checkAssetIosReleaseIds = [];
    public array $checkAssetAndroidReleaseIds = [];

    private MngMasterReleaseService $mngMasterReleaseService;
    private MngAssetReleaseService $mngAssetReleaseService;

    /** DBデータを設定するパラメータ  */
    public Collection|null $mngMasterReleasesByApply;
    public Collection $mngMasterReleases;
    public Collection|null $mngAssetReleasesIosByApply = null;
    public Collection|null $mngAssetReleasesAndroidByApply = null;
    public Collection $mngAssetReleasesByIos;
    public Collection $mngAssetReleasesByAndroid;
    public array $masterImportedAtMap = [];
    public array $assetImportedAtMap = [];
    
    public const DATA_TYPE_MASTER = 'master';
    public const DATA_TYPE_ASSET_IOS = 'assetIos';
    public const DATA_TYPE_ASSET_ANDROID = 'assetAndroid';

    public function __construct()
    {
        $this->mngMasterReleaseService = app()->make(MngMasterReleaseService::class);
        $this->mngAssetReleaseService = app()->make(MngAssetReleaseService::class);
        $this->mngMasterReleasesByApply = collect();
        $this->mngMasterReleases = collect();
        $this->mngAssetReleasesByIos = collect();
        $this->mngAssetReleasesByAndroid = collect();
    }

    public function mount(): void
    {
        // 配信中/準備中のマスターデータ、アセットデータを取得
        $this->mngMasterReleasesByApply = $this->mngMasterReleaseService->getMngMasterReleasesByApply();
        $this->mngMasterReleases = $this->mngMasterReleaseService
            ->getMngMasterReleasesByApplyOrPending()
            // target_release_version_idがnullのものは除外する
            ->reject(fn(MngMasterRelease $row) => is_null($row->target_release_version_id));
        $this->masterImportedAtMap = $this->mngMasterReleaseService->getLastImportAtMap();

        /** @var Collection $latestReleasedMngAssetReleaseCollection */
        $applyMngAssetReleaseCollection = $this->mngAssetReleaseService->getAllPlatformApplyMngAssetReleases();

        $this->mngAssetReleasesIosByApply = $applyMngAssetReleaseCollection
            ->filter(fn (MngAssetRelease $row) => $row->platform === PlatformConstant::PLATFORM_IOS);
        $this->mngAssetReleasesAndroidByApply = $applyMngAssetReleaseCollection
            ->filter(fn (MngAssetRelease $row) => $row->platform === PlatformConstant::PLATFORM_ANDROID);

        $mngAssetReleases = $this->mngAssetReleaseService->getMngAssetReleasesByApplyOrPending()
            // target_release_version_idがnullのものは除外する
            ->reject(fn (MngAssetRelease $row) => is_null($row->target_release_version_id));
        $this->mngAssetReleasesByIos = $mngAssetReleases->filter(fn (MngAssetRelease $row) => $row->platform === PlatformConstant::PLATFORM_IOS);
        $this->mngAssetReleasesByAndroid = $mngAssetReleases->filter(fn (MngAssetRelease $row) => $row->platform === PlatformConstant::PLATFORM_ANDROID);
        $this->assetImportedAtMap = $this->mngAssetReleaseService->getLastImportAtMap();
    }

    /**
     * importedAtMapから対象idのimportedAtを取得する
     *
     * @param array<string, Carbon> $map
     * @param string|null $key
     * @return string
     */
    private function getImportedAtByMap(array $map, ?string $key): string
    {
        if (empty($map) || is_null($key)) {
            // mapが空またはkeyがnullだったら空文字を返す
            return '';
        }

        return isset($map[$key])
            ? $map[$key]
                ->timezone(config('wp_common_admin.form_input_time_zone'))
                ->format('Y/m/d H:i:s')
            : '';
    }

    /**
     * 現在適用中のマスターリリース情報を取得
     *
     * @return array
     */
    private function getCurrentMasterData(): array
    {
        $currentMasterDataList = [];
        foreach ($this->mngMasterReleasesByApply as $mngMasterReleaseApply) {
            /** @var MngMasterReleaseVersion|null $mngMasterReleaseVersion */
            $mngMasterReleaseVersion = $mngMasterReleaseApply->mngMasterReleaseVersion;

            $importedAt = $this->getImportedAtByMap(
                $this->masterImportedAtMap,
                $mngMasterReleaseApply->target_release_version_id
            );

            $currentMasterDataList[] = [
                'releaseKey' => $mngMasterReleaseApply->release_key,
                'clientCompatibilityVersion' => $mngMasterReleaseApply->client_compatibility_version,
                'description' => $mngMasterReleaseApply->description,
                'dataHash' => $mngMasterReleaseVersion->data_hash,
                'importedAt' => $importedAt,
            ];
        }

        return $currentMasterDataList;
    }

    /**
     * 現在適用中のアセットリリース情報を取得
     * @return array[]
     */
    private function getCurrentAssetData(): array
    {
        $currentAssets = [];

        /** @var MngAssetReleaseVersion $version */
        foreach ($this->mngAssetReleasesIosByApply as $mngAssetRelease) {
            $importAtIos = $this->getImportedAtByMap(
                $this->assetImportedAtMap,
                $mngAssetRelease->target_release_version_id
            );
            $assetVersion = $mngAssetRelease->mngAssetReleaseVersion->first();
            $currentAssets[PlatformConstant::PLATFORM_IOS][] = [
                'releaseKey' => $mngAssetRelease->release_key,
                'clientCompatibilityVersion' => $mngAssetRelease->client_compatibility_version,
                'description' => $mngAssetRelease->description,
                'dataHash' => $assetVersion->catalog_hash,
                'importedAt' => $importAtIos
            ];
        }

        /** @var MngAssetReleaseVersion $version */
        foreach ($this->mngAssetReleasesAndroidByApply as $mngAssetRelease) {
            $importAtIos = $this->getImportedAtByMap(
                $this->assetImportedAtMap,
                $mngAssetRelease->target_release_version_id
            );
            $assetVersion = $mngAssetRelease->mngAssetReleaseVersion->first();
            $currentAssets[PlatformConstant::PLATFORM_ANDROID][] = [
                'releaseKey' => $mngAssetRelease->release_key,
                'clientCompatibilityVersion' => $mngAssetRelease->client_compatibility_version,
                'description' => $mngAssetRelease->description,
                'dataHash' => $assetVersion->catalog_hash,
                'importedAt' => $importAtIos
            ];
        }

        return $currentAssets;
    }

    /**
     * 配信切り替え対象のマスターリリースデータを取得
     *
     * @return array
     */
    private function getTargetMasterData(): array
    {
        $targetMasterDataList = [];
        $applyReleaseKeys = $this->mngMasterReleasesByApply->pluck('release_key')->toArray();

        // 配信準備中のMngMasterReleaseを取得する
        foreach ($this->mngMasterReleases as $mngMasterRelease) {
            if (in_array($mngMasterRelease->release_key, $applyReleaseKeys, true)) {
                continue;
            }
            /** @var MngMasterReleaseVersion|null $mngMasterReleaseVersion */
            $mngMasterReleaseVersion = $mngMasterRelease->mngMasterReleaseVersion;
            $importedAt = $this->getImportedAtByMap(
                $this->masterImportedAtMap,
                $mngMasterRelease->target_release_version_id
            );
            
            $targetMasterDataList[] = [
                'id' => $mngMasterRelease->id,
                'releaseKey' => $mngMasterRelease->release_key,
                'clientCompatibilityVersion' => $mngMasterRelease->client_compatibility_version,
                'description' => $mngMasterRelease->description,
                'dataHash' => $mngMasterReleaseVersion?->data_hash,
                'importedAt' => $importedAt,
            ];
        }
        
        return $targetMasterDataList;
    }

    /**
     * 配信切り替え対象のアセットリリースデータを取得
     *
     * @return array
     */
    private function getTargetAssetData(): array
    {
        $targetAssetDataList = [
            PlatformConstant::PLATFORM_IOS => [],
            PlatformConstant::PLATFORM_ANDROID => [],
        ];
        $applyReleaseKeysIos = $this->mngAssetReleasesIosByApply->pluck('release_key')->toArray();
        $applyReleaseKeysAndroid = $this->mngAssetReleasesAndroidByApply->pluck('release_key')->toArray();

        // 一度配列化してiosとandroidで処理を統一している
        $data = [
            PlatformConstant::PLATFORM_IOS => [
                'mngAssetReleases' => $this->mngAssetReleasesByIos,
                'applyReleaseKeys' => $applyReleaseKeysIos
            ],
            PlatformConstant::PLATFORM_ANDROID => [
                'mngAssetReleases' => $this->mngAssetReleasesByAndroid,
                'applyReleaseKeys' => $applyReleaseKeysAndroid
            ],
        ];
        foreach ($data as $platform => $sources) {
            $mngAssetReleases = $sources['mngAssetReleases'];
            $applyReleaseKeys = $sources['applyReleaseKeys'];

            /** @var MngAssetRelease $mngAssetRelease */
            foreach ($mngAssetReleases as $mngAssetRelease) {
                if (in_array($mngAssetRelease->release_key, $applyReleaseKeys, true)) {
                    continue;
                }
                $importAtIos = $this->getImportedAtByMap(
                    $this->assetImportedAtMap,
                    $mngAssetRelease->target_release_version_id
                );

                /** @var MngAssetReleaseVersion $assetVersion */
                $assetVersion = $mngAssetRelease->mngAssetReleaseVersion->first();
                $targetAssetDataList[$platform][] = [
                    'id' => $mngAssetRelease->id,
                    'releaseKey' => $mngAssetRelease->release_key,
                    'clientCompatibilityVersion' => $mngAssetRelease->client_compatibility_version,
                    'description' => $mngAssetRelease->description,
                    'dataHash' => $assetVersion->catalog_hash,
                    'importedAt' => $importAtIos
                ];
            }
        }

        return $targetAssetDataList;
    }

    /**
     * @return array[]
     */
    protected function getViewData(): array
    {
        $currentMasterData = $this->getCurrentMasterData();
        $currentAssetData = $this->getCurrentAssetData();
        $targetMasterData = $this->getTargetMasterData();
        $targetAssetData = $this->getTargetAssetData();
        return [
            'headingNames' => [
                self::DATA_TYPE_MASTER => 'マスターデータ',
                self::DATA_TYPE_ASSET_IOS => 'アセットデータ(ios)',
                self::DATA_TYPE_ASSET_ANDROID => 'アセットデータ(Android)',
            ],
            'currentData' => [
                self::DATA_TYPE_MASTER => $currentMasterData,
                self::DATA_TYPE_ASSET_IOS  => $currentAssetData[PlatformConstant::PLATFORM_IOS] ?? [],
                self::DATA_TYPE_ASSET_ANDROID => $currentAssetData[PlatformConstant::PLATFORM_ANDROID] ?? [],
            ],
            'targetData' => [
                self::DATA_TYPE_MASTER => $targetMasterData,
                self::DATA_TYPE_ASSET_IOS  => $targetAssetData[PlatformConstant::PLATFORM_IOS] ?? [],
                self::DATA_TYPE_ASSET_ANDROID => $targetAssetData[PlatformConstant::PLATFORM_ANDROID] ?? [],
            ],
        ];
    }

    /**
     * チェックしたリリースキーを保存する
     * チェックを外した場合は削除する
     *
     * @param string $status
     * @param string $mngReleaseId
     * @return void
     */
    public function onCheckMngReleaseId(string $status, string $mngReleaseId): void
    {
        if (count($this->checkMasterReleaseIds) > MasterData::MASTER_RELEASE_APPLY_LIMIT) {
            Notification::make()
                ->title('マスターリリースキーの切り替えは2件以上行えません')
                ->color('danger')
                ->danger()
                ->persistent()
                ->send();
            return;
        }
        if (count($this->checkAssetIosReleaseIds) > AssetData::ASSET_RELEASE_APPLY_LIMIT) {
            Notification::make()
                ->title('アセットリリースキー(ios)の切り替えは2件以上行えません')
                ->color('danger')
                ->danger()
                ->persistent()
                ->send();
            return;
        }
        if (count($this->checkAssetAndroidReleaseIds) > AssetData::ASSET_RELEASE_APPLY_LIMIT) {
            Notification::make()
                ->title('アセットリリースキー(android)の切り替えは2件以上行えません')
                ->color('danger')
                ->danger()
                ->persistent()
                ->send();
            return;
        }

        // チェックしたmngMasterRelease.idを保存/削除
        $master = function () use ($mngReleaseId) {
            if (in_array($mngReleaseId, $this->checkMasterReleaseIds, true)) {
                $this->checkMasterReleaseIds = array_values(array_filter($this->checkMasterReleaseIds, fn ($checkId) => $checkId !== $mngReleaseId));
            } else {
                $this->checkMasterReleaseIds[] = $mngReleaseId;
            }
        };
        // チェックしたmngAssetRelease.idを保存/削除(ios)
        $assetIos = function () use ($mngReleaseId) {
            if (in_array($mngReleaseId, $this->checkAssetIosReleaseIds, true)) {
                $this->checkAssetIosReleaseIds = array_values(array_filter($this->checkAssetIosReleaseIds, fn ($checkId) => $checkId !== $mngReleaseId));
            } else {
                $this->checkAssetIosReleaseIds[] = $mngReleaseId;
            }
        };
        // チェックしたmngAssetRelease.idを保存/削除(android)
        $assetAndroid = function () use ($mngReleaseId) {
            if (in_array($mngReleaseId, $this->checkAssetAndroidReleaseIds, true)) {
                $this->checkAssetAndroidReleaseIds = array_values(array_filter($this->checkAssetAndroidReleaseIds, fn ($checkId) => $checkId !== $mngReleaseId));
            } else {
                $this->checkAssetAndroidReleaseIds[] = $mngReleaseId;
            }
        };

        // $statusに応じて処理内容を実行
        match($status) {
            self::DATA_TYPE_MASTER => $master(),
            self::DATA_TYPE_ASSET_IOS => $assetIos(),
            self::DATA_TYPE_ASSET_ANDROID => $assetAndroid(),
        };
    }

    /**
     * @return array
     */
    public function getFormActions(): array
    {
        return [
            $this->executeButton(),
        ];
    }

    /**
     * 実行ボタン
     *
     * @return Action
     */
    private function executeButton(): Action
    {
        return Action::make('execute')
            ->label('実行')
            ->requiresConfirmation()
            ->modalHeading('必ず内容を確認してください')
            ->modalDescription('')
            ->modalIconColor('danger')
            ->modalContent(function () {
                // 変更前後の差分を表示する
                // マスターデータ
                [$applyMngMasterReleases, $expiredMngMasterReleases] = $this->makeConfirmModalDataByMasterRelease();

                // アセットデータ(ios)
                [$applyMngAssetReleasesIos, $expiredMngAssetReleasesIos] = $this
                    ->makeConfirmModalDataByAssetRelease($this->mngAssetReleasesIosByApply, $this->checkAssetIosReleaseIds, $this->mngAssetReleasesByIos);

                // アセットデータ(android)
                [$applyMngAssetReleasesAndroid, $expiredMngAssetReleasesAndroid] = $this
                    ->makeConfirmModalDataByAssetRelease($this->mngAssetReleasesAndroidByApply, $this->checkAssetAndroidReleaseIds, $this->mngAssetReleasesByAndroid);

                return view($this->confirmModalView,
                    [
                        'statusNames' => [
                            'applyData' => '配信中',
                            'expiredData' => '配信終了'
                        ],
                        'viewData' => [
                            'マスターデータ' => [
                                'expiredData' => $expiredMngMasterReleases,
                                'applyData' => $applyMngMasterReleases,
                            ],
                            'アセットデータ(ios)' => [
                                'expiredData' => $expiredMngAssetReleasesIos,
                                'applyData' => $applyMngAssetReleasesIos,
                            ],
                            'アセットデータ(android)' => [
                                'expiredData' => $expiredMngAssetReleasesAndroid,
                                'applyData' => $applyMngAssetReleasesAndroid,
                            ],
                        ]
                    ]
                );
            })
            ->modalWidth(MaxWidth::SevenExtraLarge)
            ->action(fn () => $this->execute())
            ->failureNotification(fn ($e) => 'リリース更新に失敗しました')
            ->disabled(function () {
                // チェックされたリリース情報がなければボタンを非活性にする
                return $this->checkMasterReleaseIds === []
                    && $this->checkAssetIosReleaseIds === []
                    && $this->checkAssetAndroidReleaseIds === [];
            });
    }

    /**
     * 確認モーダル表示用 マスターリリース情報を表示する
     *
     * @return array
     */
    private function makeConfirmModalDataByMasterRelease(): array
    {
        // 現在配信中のidとチェックしたidを配列に統合して更新後の配信中/配信終了idを取得する
        $applyReleaseIds = $this->mngMasterReleasesByApply->pluck('id')->toArray();
        $mergeReleaseIds = array_merge($applyReleaseIds, $this->checkMasterReleaseIds);
        if (count($mergeReleaseIds) > MasterData::MASTER_RELEASE_APPLY_LIMIT) {
            // 統合したリリース情報が3件以上あれば、配信中と配信終了に分割してidの配列を作る
            // 現在配信中のリリース情報とチェックした準備中のリリース情報を統合し、release_key降順ソートして先頭の2つを配信中、それ以外を配信終了とする
            $mergeMasterReleases = $this->mngMasterReleases
                ->filter(fn (MngMasterRelease $mngMasterRelease) => in_array($mngMasterRelease->id, $mergeReleaseIds, true));
            $sortedMasterReleaseIds = $mergeMasterReleases
                ->sortByDesc('release_key')
                ->pluck('id')
                ->toArray();
            [$applyReleaseIds, $expiredReleaseIds] = array_chunk($sortedMasterReleaseIds, MasterData::MASTER_RELEASE_APPLY_LIMIT);
        } else {
            // 2件以内なら全て配信中にする
            $applyReleaseIds = $mergeReleaseIds;
            $expiredReleaseIds = [];
        }
        
        $applyMngMasterReleases = $this->mngMasterReleases
            ->filter(fn (MngMasterRelease $mngMasterRelease) => in_array($mngMasterRelease->id, $applyReleaseIds, true))
            ->map(function (MngMasterRelease $mngMasterRelease) {
                $version = $mngMasterRelease->mngMasterReleaseVersion;
                return [
                    'releaseKey' => $mngMasterRelease->release_key,
                    'client_version' => $mngMasterRelease->client_compatibility_version,
                    'description' => $mngMasterRelease->description,
                    'dataHash' => $version->data_hash,
                ];
            })
            ->toArray();
        $expiredMngMasterReleases = $this->mngMasterReleases
            ->filter(fn (MngMasterRelease $mngMasterRelease) => in_array($mngMasterRelease->id, $expiredReleaseIds, true))
            ->map(function (MngMasterRelease $mngMasterRelease) {
                $version = $mngMasterRelease->mngMasterReleaseVersion;
                return [
                    'releaseKey' => $mngMasterRelease->release_key,
                    'client_version' => $mngMasterRelease->client_compatibility_version,
                    'description' => $mngMasterRelease->description,
                    'dataHash' => $version->data_hash,
                ];
            })
            ->toArray();

        return [
            $applyMngMasterReleases,
            $expiredMngMasterReleases,
        ];
    }

    /**
     * 確認モーダル表示用 アセットリリース情報を表示する
     *
     * @param Collection $mngAssetReleaseByApply
     * @param array $checkAssetReleaseIds
     * @param Collection $mngAssetReleases
     * @return array
     */
    private function makeConfirmModalDataByAssetRelease(
        Collection $mngAssetReleaseByApply,
        array $checkAssetReleaseIds,
        Collection $mngAssetReleases
    ): array {
        // 現在配信中のidとチェックしたidを配列に統合して更新後の配信中/配信終了idを取得する
        $applyAssetReleaseIds = $mngAssetReleaseByApply->pluck('id')->toArray();
        $mergeAssetReleaseIds = array_merge($applyAssetReleaseIds, $checkAssetReleaseIds);
        if (count($mergeAssetReleaseIds) > AssetData::ASSET_RELEASE_APPLY_LIMIT) {
            // 統合したリリース情報が3件以上あれば、配信中と配信終了に分割してidの配列を作る
            // 現在配信中のリリース情報とチェックした準備中のリリース情報を統合し、release_key降順ソートして先頭の2つを配信中、それ以外を配信終了とする
            $mergeAssetReleasesIos = $mngAssetReleases
                ->filter(fn (MngAssetRelease $mngAssetRelease) => in_array($mngAssetRelease->id, $mergeAssetReleaseIds, true));
            $sortedAssetReleaseIosIds = $mergeAssetReleasesIos
                ->sortByDesc('release_key')
                ->pluck('id')
                ->toArray();
            [$applyAssetReleaseIds, $expiredAssetReleaseIds] = array_chunk($sortedAssetReleaseIosIds, AssetData::ASSET_RELEASE_APPLY_LIMIT);
        } else {
            // 2件以内なら全て配信中にする
            $applyAssetReleaseIds = $mergeAssetReleaseIds;
            $expiredAssetReleaseIds = [];
        }
        $applyMngAssetReleasesIos = $mngAssetReleases
            ->filter(fn (MngAssetRelease $mngAssetRelease) => in_array($mngAssetRelease->id, $applyAssetReleaseIds, true))
            ->map(function (MngAssetRelease $mngAssetRelease) {
                $version = $mngAssetRelease->mngAssetReleaseVersion->first();
                return [
                    'releaseKey' => $mngAssetRelease->release_key,
                    'client_version' => $mngAssetRelease->client_compatibility_version,
                    'description' => $mngAssetRelease->description,
                    'dataHash' => $version->catalog_hash,
                ];
            })
            ->toArray();
        $expiredMngAssetReleasesIos = $mngAssetReleases
            ->filter(fn (MngAssetRelease $mngAssetRelease) => in_array($mngAssetRelease->id, $expiredAssetReleaseIds, true))
            ->map(function (MngAssetRelease $mngAssetRelease) {
                $version = $mngAssetRelease->mngAssetReleaseVersion->first();
                return [
                    'releaseKey' => $mngAssetRelease->release_key,
                    'client_version' => $mngAssetRelease->client_compatibility_version,
                    'description' => $mngAssetRelease->description,
                    'dataHash' => $version->catalog_hash,
                ];
            })
            ->toArray();

        return [
            $applyMngAssetReleasesIos,
            $expiredMngAssetReleasesIos,
        ];
    }

    /**
     * @return void
     */
    private function execute(): void
    {
        try {
            $this->transaction(function () {
                // 適用中のデータがないまたは適用中のデータと変更があるものを更新
                foreach ($this->checkMasterReleaseIds as $mngMasterReleaseId) {
                    $this->mngMasterReleaseService
                        ->releasedMngMasterReleasesById($mngMasterReleaseId);
                }
                foreach ($this->checkAssetIosReleaseIds as $mngAssetReleaseIosId) {
                    $this->mngAssetReleaseService
                        ->releasedMngAssetReleasesById($mngAssetReleaseIosId);
                }
                foreach ($this->checkAssetAndroidReleaseIds as $mngAssetReleaseAndroidId) {
                    $this->mngAssetReleaseService
                        ->releasedMngAssetReleasesById($mngAssetReleaseAndroidId);
                }
            }, [DBUtility::getMngConnName()]);

            // キャッシュを削除
            $this->deleteMngMasterReleaseVersionCache();
            $this->deleteMngAssetReleaseVersionCache();

        } catch (\Exception $e) {
            Notification::make()
                ->title('リリース更新に失敗しました')
                ->body('サーバー管理者にお問い合わせください。')
                ->danger() // 通知のアイコンを指定
                ->color('danger') // 通知の背景色を指定
                ->send();
            Log::error('', [$e]);
            return;
        }

        Notification::make()
            ->title('リリース更新が完了しました')
            ->success()
            ->send();

        $slug = self::$slug;
        redirect()->to("/admin/{$slug}");
    }
}
