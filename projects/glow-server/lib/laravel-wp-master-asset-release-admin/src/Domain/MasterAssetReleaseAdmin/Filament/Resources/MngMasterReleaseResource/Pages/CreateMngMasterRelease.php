<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseResource;

/**
 * マスターリリース新規作成画面
 */
class CreateMngMasterRelease extends CreateRecord
{
    protected static string $resource = MngMasterReleaseResource::class;

    // 連続して作成ボタンを非表示
    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
