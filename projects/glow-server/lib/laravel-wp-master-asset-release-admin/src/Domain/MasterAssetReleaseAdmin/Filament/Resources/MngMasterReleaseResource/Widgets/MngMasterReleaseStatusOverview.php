<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseResource\Widgets;

use Filament\Widgets\Widget;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngMasterReleaseService;

class MngMasterReleaseStatusOverview extends Widget
{
    protected static string $view = 'view-master-asset-admin::filament.resources.mng-master-release-resource.widgets.mng-master-release-status-overview';

    public MngMasterRelease|null $mngMasterRelease = null;

    /**
     * 画面遷移時に初回だけ起動
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function mount(): void
    {
        /** @var MngMasterReleaseService $mngMasterReleaseService */
        $mngMasterReleaseService = app()->make(MngMasterReleaseService::class);
        /** @var MngMasterRelease|null $mngMasterRelease */
        $this->mngMasterRelease = $mngMasterReleaseService->getLatestReleasedMngMasterRelease();
    }

    /**
     * @return array|array[]|mixed[]
     */
    protected function getViewData(): array
    {
        $response = [];
        if (!is_null($this->mngMasterRelease)) {
            $response = [
                'result' => [
                    'releaseKey' => $this->mngMasterRelease->release_key,
                    'dataHash' => $this->mngMasterRelease->mngMasterReleaseVersion->data_hash,
                ],
            ];
        }

        return $response;
    }
}
