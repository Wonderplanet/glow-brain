<?php

namespace App\Filament\Pages\MngMasterReleases;

use App\Filament\Authorizable;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Pages\MngMasterReleases\ImportFromSpreadSheet as BaseImportFromSpreadSheet;

/**
 * マスターデータインポートv2管理ツールページクラス
 * スプレッドシートからマスタデータシートの情報を取得/表示する
 */
class ImportFromSpreadSheet extends BaseImportFromSpreadSheet
{
    use Authorizable;
}
