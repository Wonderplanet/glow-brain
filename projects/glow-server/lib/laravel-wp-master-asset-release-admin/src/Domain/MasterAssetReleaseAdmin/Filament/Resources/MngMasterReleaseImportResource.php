<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources;

use App\Filament\Authorizable;
use Filament\Resources\Resource;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseImportResource\Pages;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;

/**
 * マスターデータ環境間インポート画面リソースクラス
 */
class MngMasterReleaseImportResource extends Resource
{
    use Authorizable;

    protected static ?string $navigationIcon = 'heroicon-m-arrow-right-end-on-rectangle';
    protected static ?string $model = MngMasterRelease::class;
    protected static ?int $navigationSort = -996;
    protected static ?string $navigationGroup = 'v2 マスター・アセット管理';
    protected static ?string $navigationLabel = 'マスターデータ環境間インポート';
    protected static ?string $modelLabel = 'マスターデータ配信管理ダッシュボード';

    /**
     * ページ設定
     *
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ImportMngMasterRelease::route('/'),
            'import' => Pages\ImportMngMasterRelease::route('/import'),
            'diff' => Pages\DiffFromEnvironment::route('/{importId}/diff'),
        ];
    }
}
