<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources;

use Filament\Resources\Resource;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseImportResource\Pages;

/**
 * アセットリリースインポート画面リソースクラス
 */
class MngAssetReleaseImportResource extends Resource
{
    protected static ?string $model = MngAssetRelease::class;
    protected static ?int $navigationSort = -995;
    protected static ?string $navigationIcon = 'heroicon-m-arrow-right-end-on-rectangle';
    protected static ?string $navigationGroup = 'v2 マスター・アセット管理';
    protected static ?string $navigationLabel = 'アセット環境間インポート';
    protected static ?string $modelLabel = 'アセット配信管理ダッシュボード';

    /**
     * ページ設定
     *
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ImportMngAssetRelease::route('/'),
            'import' => Pages\ImportMngAssetRelease::route('/import'),
            'confirm' => Pages\ConfirmMngAssetRelease::route('/confirm'),
            'list' => MngAssetReleaseResource\Pages\ListMngAssetReleases::route('/list'),
        ];
    }
}
