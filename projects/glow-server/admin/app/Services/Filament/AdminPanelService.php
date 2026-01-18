<?php

namespace App\Services\Filament;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngMasterReleaseService;

class AdminPanelService
{
    /**
     * マスターDBのコネクション先を配信中のリリースバージョンDBに切り替える
     * 配信中のリリースキーがなければ変更しない
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function setMstDatabaseConnection(): void
    {
        /** @var MngMasterReleaseService $mngMasterReleaseService */
        $mngMasterReleaseService = app()->make(MngMasterReleaseService::class);

        /** @var MngMasterRelease|null $mngMasterRelease */
        $mngMasterRelease = $mngMasterReleaseService->getLatestReleasedMngMasterRelease();

        if (!is_null($mngMasterRelease)) {
            /** @var MngMasterReleaseVersion $mngMasterReleaseVersion */
            $mngMasterReleaseVersion = $mngMasterRelease->mngMasterReleaseVersion;

            // TODO: かなり強引なので、より安全な方法を検討する
            Config::set("database.connections.mst.database", $mngMasterReleaseVersion->getDbName());
            DB::purge('mst');
        }
    }
}
