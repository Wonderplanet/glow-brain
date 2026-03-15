<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseResource;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngMasterReleaseService;

/**
 * マスターリリース一覧画面
 */
class ListMngMasterReleases extends ListRecords
{
    protected static string $resource = MngMasterReleaseResource::class;

    /**
     * @return array|Actions\Action[]|Actions\ActionGroup[]
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('スプレッドシートインポート')->url('mng-master-release-versions/import-from-spread-sheet'),
            Actions\CreateAction::make()->label('環境間インポート')->url('mng-master-release-imports'),
            Actions\CreateAction::make()->label('リリースの追加'),
        ];
    }

    /**
     * @return array|Tab[]
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getTabs(): array
    {
        /** @var MngMasterReleaseService $service */
        $service = app()->make(MngMasterReleaseService::class);

        // 配信中ステータスのMngMasterReleaseを取得
        $oldestMngMasterReleasesApply = $service->getOldestApplyMngMasterRelease();
        // 配信中ステータスのrelease_keyのうち、一番古いものを取得
        // 配信中ステータスの情報がない場合は0を設定
        // 実際の表示ではmng_master_releasesにデータがないので何も表示されない
        $oldestReleaseKeyApply = $oldestMngMasterReleasesApply->release_key ?? 0;

        return [
            'main' => Tab::make('配信中/準備中')
                ->query(function ($query) use ($oldestReleaseKeyApply) {
                    // 「配信中」または「配信準備中」データを取得
                    // 「配信終了」以外のデータを取得する条件としている
                    $query->whereNot(function ($query) use ($oldestReleaseKeyApply) {
                        $query
                            ->where('enabled', 1)
                            ->whereNotNull('target_release_version_id')
                            ->where('release_key', '<', $oldestReleaseKeyApply);
                    });
                }),
            'expired' => Tab::make('配信終了')
                ->query(function ($query) use ($oldestReleaseKeyApply) {
                    // 「配信終了」データを取得
                    $query
                        ->where('enabled', 1)
                        ->whereNotNull('target_release_version_id')
                        ->where('release_key', '<', $oldestReleaseKeyApply);
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
}
