<?php

namespace App\Filament\Resources;

use App\Filament\Authorizable;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseImportResource as BaseMngAssetReleaseImportResource;

/**
 * アセットリリースインポート画面リソースクラス
 */
class MngAssetReleaseImportResource extends BaseMngAssetReleaseImportResource
{
    use Authorizable;
}
