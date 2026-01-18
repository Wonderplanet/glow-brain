<?php

namespace App\Filament\Pages\MngMasterReleases;

use App\Filament\Authorizable;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Pages\MngMasterReleases\MngMasterAndAssetReleaseUpdate as BaseMngMasterAndAssetReleaseUpdate;

/**
 * マスターデータインポートv2管理ツールページクラス
 * マスターデータ/アセットデータのリリースを処理する
 */
class MngMasterAndAssetReleaseUpdate extends BaseMngMasterAndAssetReleaseUpdate
{
    use Authorizable;
}
