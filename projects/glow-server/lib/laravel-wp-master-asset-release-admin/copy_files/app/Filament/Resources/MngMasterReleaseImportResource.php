<?php

namespace App\Filament\Resources;

use App\Filament\Authorizable;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseImportResource as BaseMngMasterReleaseImportResource;

/**
 * マスターデータ環境間インポート画面リソースクラス
 */
class MngMasterReleaseImportResource extends BaseMngMasterReleaseImportResource
{
    use Authorizable;
}
