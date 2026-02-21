<?php

namespace App\Filament\Resources;

use App\Filament\Authorizable;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseResource as BaseMngAssetReleaseResource;

/**
 * アセットリリース管理画面リソースクラス
 */
class MngAssetReleaseResource extends BaseMngAssetReleaseResource
{
    use Authorizable;
}
