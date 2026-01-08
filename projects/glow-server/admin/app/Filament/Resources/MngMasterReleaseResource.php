<?php

namespace App\Filament\Resources;

use App\Filament\Authorizable;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseResource as BaseMngMasterReleaseResource;

/**
 * マスターリリース管理画面リソースクラス
 */
class MngMasterReleaseResource extends BaseMngMasterReleaseResource
{
    use Authorizable;
}
