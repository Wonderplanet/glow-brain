<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseResource;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngAssetReleaseService;

/**
 * アセットリリース一覧画面
 */
class ListMngAssetReleases extends ListRecords
{
    protected static string $resource = MngAssetReleaseResource::class;

    public string $iosJenkinsUrl = "";
    public string $androidJenkinsUrl = "";

    /**
     * @var MngAssetReleaseService
     */
    private MngAssetReleaseService $service;

    public function __construct()
    {
        $this->service = app()->make(MngAssetReleaseService::class);
        // 表示用jenkinsURLを設定
        $jenkinsUrlList = $this->service->getAssetCreateJenkinsUrlList();
        $this->iosJenkinsUrl = $jenkinsUrlList['iOS'];
        $this->androidJenkinsUrl = $jenkinsUrlList['Android'];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('環境間インポート')
                ->url(self::getResource()::getUrl('import'))->disabled(function () {
                    // 最新のリリース済みMngAssetReleaseを取得
                    $latestReleaseKeyAndroid = $this->service->getLatestReleaseKey(PlatformConstant::PLATFORM_ANDROID);
                    $latestReleaseKeyIos = $this->service->getLatestReleaseKey(PlatformConstant::PLATFORM_IOS);

                    // 配信ステータス「配信中」「準備中」のアセットリリース情報を取得
                    // 両プラットフォームとも無ければインポートは不可能なので、ボタンを非活性にする
                    $effectiveAssetReleaseLitAndroid = $this->service->getEffectiveAssetReleaseList(PlatformConstant::PLATFORM_ANDROID, $latestReleaseKeyAndroid);
                    $effectiveAssetReleaseLitIos = $this->service->getEffectiveAssetReleaseList(PlatformConstant::PLATFORM_ANDROID, $latestReleaseKeyIos);
                    if ($effectiveAssetReleaseLitAndroid->isEmpty() && $effectiveAssetReleaseLitIos->isEmpty()) {
                        return true;
                    }

                    return false;
                }),
            Actions\CreateAction::make()->label('リリースの追加'),
        ];
    }

    public function getTabs(): array
    {
        /** @var MngAssetReleaseService $service */
        $service = app()->make(MngAssetReleaseService::class);

        // 配信中の情報から一番古いリリースキーを取得(配信中データがなければ0)
        $oldestReleaseKeyAndroid = $service->getOldestApplyMngAssetReleaseKey(PlatformConstant::PLATFORM_ANDROID);
        $oldestReleaseKeyIos = $service->getOldestApplyMngAssetReleaseKey(PlatformConstant::PLATFORM_IOS);

        return [
            'main' => Tab::make('配信中/準備中')
                ->query(function ($query) use ($oldestReleaseKeyAndroid, $oldestReleaseKeyIos) {
                    $query
                        ->where(function ($query) use ($oldestReleaseKeyAndroid) {
                            return $this->getStatusApplyingOrPendingQuery(PlatformConstant::PLATFORM_ANDROID, $query, $oldestReleaseKeyAndroid);
                        })
                        ->orWhere(function ($query) use ($oldestReleaseKeyIos) {
                            return $this->getStatusApplyingOrPendingQuery(PlatformConstant::PLATFORM_IOS, $query, $oldestReleaseKeyIos);
                        });
                    return $query;
                }),
            'expired' => Tab::make('配信終了')
                ->query(function ($query) use ($oldestReleaseKeyAndroid, $oldestReleaseKeyIos) {
                    $query
                        ->where(function ($query) use ($oldestReleaseKeyAndroid) {
                            return $this->getStatusEndQuery(PlatformConstant::PLATFORM_ANDROID, $query, $oldestReleaseKeyAndroid);
                        })
                        ->orWhere(function ($query) use ($oldestReleaseKeyIos) {
                            return $this->getStatusEndQuery(PlatformConstant::PLATFORM_IOS, $query, $oldestReleaseKeyIos);
                        });
                }),
        ];
    }

    /**
     * デフォルトで表示するタブを指定
     *
     * @return string|int|null
     */
    public function getDefaultActiveTab(): string|int|null
    {
        return 'main';
    }

    /**
     * アセット配信ステータスが「配信中」または「配信準備中」に絞り込むクエリ
     *
     * @param int $platform
     * @param Builder $query
     * @param int $oldestReleaseKey
     *
     * @return Builder
     */
    private function getStatusApplyingOrPendingQuery(int $platform, Builder $query, int $oldestReleaseKey): Builder
    {
        $query
            ->where('platform', '=', $platform)
            ->whereNot(function ($query) use ($oldestReleaseKey) {
                // 「配信中」または「配信準備中」データを取得
                // 「配信終了」以外のデータを取得する条件としている
                $query
                    ->where('enabled', 1)
                    ->whereNotNull('target_release_version_id')
                    ->where('release_key', '<', $oldestReleaseKey);
            });

        return $query;
    }

    /**
     * アセット配信ステータスが「配信済み」に絞り込むクエリ
     *
     * @param int $platform
     * @param Builder $query
     * @param int $releaseKey
     *
     * @return Builder
     */
    private function getStatusEndQuery(int $platform, Builder $query, int $releaseKey): Builder
    {
        $query
            ->where('platform', '=', $platform)
            ->where(function ($query) use ($releaseKey) {
                $query
                    ->where('enabled', 1)
                    ->whereNotNull('target_release_version_id')
                    ->where('release_key', '<', $releaseKey);
            });

        return $query;
    }
}
